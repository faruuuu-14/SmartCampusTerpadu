# ==========================================
# PEMBAHASAN / BAGIAN: Runner Evaluasi Tiga Model Wajah
# ==========================================
from __future__ import annotations

import argparse
import base64
import csv
import hashlib
import json
import os
import platform
import random
import statistics
import subprocess
import sys
import time
import urllib.error
import urllib.request
from collections import Counter, defaultdict
from pathlib import Path
from typing import Any

import numpy as np
import yaml
from dotenv import load_dotenv
from PIL import Image
from sklearn.datasets import fetch_lfw_people
from sklearn.metrics import auc, roc_curve

from evaluasi_wajah.metrik import (
    ambang_eer_validasi,
    bootstrap_peserta,
    hitung_far_frr,
    kurva_roc,
    uji_mcnemar_tepat,
)
from layanan_wajah.pengaturan import MODEL_ARCFACE, MODEL_SFACE, MODEL_YUNET

AKAR = Path(__file__).resolve().parent.parent
load_dotenv(AKAR / ".env")
KONDISI = ("terang", "redup", "sudut", "aksesori", "jarak")


def baca_konfigurasi() -> dict[str, Any]:
    with (AKAR / "evaluasi_wajah" / "konfigurasi.yaml").open(encoding="utf-8") as berkas:
        return yaml.safe_load(berkas)


def bagi_peserta(peserta: list[str], rasio_validasi: float, seed: int) -> dict[str, str]:
    peserta_acak = list(sorted(peserta))
    random.Random(seed).shuffle(peserta_acak)
    jumlah_validasi = max(4, min(len(peserta_acak) - 4, round(len(peserta_acak) * rasio_validasi)))

    if len(peserta_acak) < 8:
        raise ValueError("Protokol memerlukan minimal delapan peserta untuk split validasi/uji.")

    return {
        **{id_peserta: "validasi" for id_peserta in peserta_acak[:jumlah_validasi]},
        **{id_peserta: "uji" for id_peserta in peserta_acak[jumlah_validasi:]},
    }


def _permintaan_multipart(
    jalur_foto: Path,
    model: str,
    embedding_referensi: str | None = None,
) -> dict[str, Any]:
    batas = "----SmartCampusWajah" + str(time.time_ns())
    bagian = []
    kolom = {"model": model}
    if embedding_referensi is not None:
        kolom["referensi_base64"] = embedding_referensi
    for nama, nilai in kolom.items():
        bagian.append(
            f"--{batas}\r\nContent-Disposition: form-data; name=\"{nama}\"\r\n\r\n{nilai}\r\n".encode()
        )
    gambar = jalur_foto.read_bytes()
    nama_berkas = jalur_foto.name.replace('"', "")
    bagian.append(
        f"--{batas}\r\nContent-Disposition: form-data; name=\"foto\"; filename=\"{nama_berkas}\"\r\n"
        "Content-Type: image/jpeg\r\n\r\n".encode()
        + gambar
        + b"\r\n"
    )
    bagian.append(f"--{batas}--\r\n".encode())
    isi = b"".join(bagian)
    endpoint = "/v1/verifikasi" if embedding_referensi is not None else "/v1/embedding"
    permintaan = urllib.request.Request(
        os.getenv("EVALUASI_URL_LAYANAN", "http://127.0.0.1:8765") + endpoint,
        data=isi,
        headers={
            "Content-Type": f"multipart/form-data; boundary={batas}",
            "X-KUNCI-LAYANAN": os.getenv("WAJAH_KUNCI", ""),
        },
        method="POST",
    )
    try:
        with urllib.request.urlopen(permintaan, timeout=15) as respons:
            return json.loads(respons.read().decode("utf-8"))
    except urllib.error.HTTPError as kesalahan:
        rincian = kesalahan.read().decode("utf-8", errors="replace")
        raise RuntimeError(f"Layanan {model} mengembalikan HTTP {kesalahan.code}: {rincian}") from kesalahan


def _decode_embedding(respons: dict[str, Any]) -> np.ndarray | None:
    if not respons.get("face_found") or not respons.get("embedding_base64"):
        return None
    return np.frombuffer(base64.b64decode(respons["embedding_base64"]), dtype="<f4").astype(np.float32)


def _cosine(probe: np.ndarray | None, referensi: np.ndarray | None) -> float:
    if probe is None or referensi is None or probe.shape != referensi.shape:
        return -1.0
    return float(np.clip(np.dot(probe, referensi), -1.0, 1.0))


def evaluasi_korpus(konfigurasi: dict[str, Any]) -> tuple[list[dict[str, Any]], dict[str, Any]]:
    jalur_data = AKAR / konfigurasi["jalur_data"]
    peserta = sorted(
        folder.name
        for folder in jalur_data.iterdir()
        if folder.is_dir() and not folder.name.startswith("_")
    )
    split = bagi_peserta(peserta, float(konfigurasi["rasio_validasi"]), int(konfigurasi["seed_acak"]))
    targets = int(konfigurasi["target_impostor_minimum"])
    baris_semua: list[dict[str, Any]] = []
    latensi: dict[str, list[float]] = {}

    for model in konfigurasi["model"]:
        templat: dict[str, np.ndarray | None] = {}
        embedding_probe: dict[tuple[str, str], tuple[np.ndarray | None, float]] = {}
        contoh_foto: Path | None = None

        for id_peserta in peserta:
            foto_referensi = sorted((jalur_data / id_peserta / "referensi").glob("*.jpg"))
            embedding_referensi = []
            for foto in foto_referensi:
                respons = _permintaan_multipart(foto, model)
                nilai = _decode_embedding(respons)
                if nilai is not None:
                    embedding_referensi.append(nilai)
                    contoh_foto = contoh_foto or foto
            templat[id_peserta] = (
                np.mean(np.vstack(embedding_referensi), axis=0)
                if embedding_referensi
                else None
            )
            if templat[id_peserta] is not None:
                templat[id_peserta] /= np.linalg.norm(templat[id_peserta])

            for kondisi in KONDISI:
                for foto in sorted((jalur_data / id_peserta / kondisi).glob("*.jpg")):
                    respons = _permintaan_multipart(foto, model)
                    embedding = _decode_embedding(respons)
                    embedding_probe[(id_peserta, str(foto.relative_to(jalur_data)))] = (
                        embedding,
                        float(respons.get("processing_ms", 0)),
                    )
                    contoh_foto = contoh_foto or foto

        if contoh_foto is None:
            raise ValueError("Dataset tidak mempunyai foto yang dapat digunakan.")

        for _ in range(int(konfigurasi["pemanasan_latensi"])):
            _permintaan_multipart(contoh_foto, model)
        ukuran_latensi = []
        for _ in range(int(konfigurasi["latensi_ulangan"])):
            ukuran_latensi.append(float(_permintaan_multipart(contoh_foto, model)["processing_ms"]))
        latensi[model] = ukuran_latensi

        for id_peserta in peserta:
            target_impostor = [
                target
                for target in peserta
                if target != id_peserta and split[target] == split[id_peserta]
            ]
            generator = random.Random(f"{konfigurasi['seed_acak']}:{id_peserta}:{model}")
            if len(target_impostor) < targets:
                raise ValueError(
                    f"Split {split[id_peserta]} peserta {id_peserta} memiliki kurang dari "
                    f"{targets} target impostor berbeda."
                )
            generator.shuffle(target_impostor)
            target_impostor = target_impostor[:targets]

            for kondisi in KONDISI:
                foto_probe = sorted((jalur_data / id_peserta / kondisi).glob("*.jpg"))
                for nomor, foto in enumerate(foto_probe):
                    kunci = (id_peserta, str(foto.relative_to(jalur_data)))
                    probe, waktu = embedding_probe[kunci]
                    id_pasangan = f"{id_peserta}:{kondisi}:{nomor}"
                    baris_semua.append(
                        {
                            "model": model,
                            "split": split[id_peserta],
                            "jenis": "genuine",
                            "probe_id": id_peserta,
                            "target_id": id_peserta,
                            "kondisi": kondisi,
                            "pasangan_id": id_pasangan,
                            "skor": _cosine(probe, templat[id_peserta]),
                            "wajah_ditemukan": probe is not None,
                            "processing_ms": waktu,
                        }
                    )
                    for target in target_impostor:
                        baris_semua.append(
                            {
                                "model": model,
                                "split": split[id_peserta],
                                "jenis": "impostor",
                                "probe_id": id_peserta,
                                "target_id": target,
                                "kondisi": kondisi,
                                "pasangan_id": f"{id_pasangan}:{target}",
                                "skor": _cosine(probe, templat[target]),
                                "wajah_ditemukan": probe is not None,
                                "processing_ms": waktu,
                            }
                        )

        for jenis_serangan in ("cetak", "layar"):
            dasar = jalur_data / "_serangan" / jenis_serangan
            for folder in sorted(dasar.iterdir()):
                if not folder.is_dir() or folder.name not in split:
                    continue
                for nomor, foto in enumerate(sorted(folder.glob("*.jpg"))):
                    respons = _permintaan_multipart(foto, model, _encode(templat[folder.name]))
                    baris_semua.append(
                        {
                            "model": model,
                            "split": split[folder.name],
                            "jenis": f"serangan_{jenis_serangan}",
                            "probe_id": folder.name,
                            "target_id": folder.name,
                            "kondisi": jenis_serangan,
                            "pasangan_id": f"serangan:{jenis_serangan}:{folder.name}:{nomor}",
                            "skor": float(respons["score"]) if respons.get("score") is not None else -1.0,
                            "wajah_ditemukan": bool(respons.get("face_found")),
                            "processing_ms": float(respons.get("processing_ms", 0)),
                        }
                    )

    return baris_semua, {"latensi": latensi, "split": split, "mode": "PENELITIAN"}


def _encode(embedding: np.ndarray | None) -> str:
    if embedding is None:
        raise ValueError("Templat referensi tidak berhasil dibuat dari wajah yang terdeteksi.")
    return base64.b64encode(embedding.astype("<f4").tobytes()).decode("ascii")


def demo_lfw(konfigurasi: dict[str, Any]) -> tuple[list[dict[str, Any]], dict[str, Any]]:
    data = fetch_lfw_people(
        data_home=str(AKAR / "evaluasi_wajah" / "data_wajah" / "demo_lfw"),
        min_faces_per_person=4,
        resize=0.5,
        color=False,
        download_if_missing=True,
    )
    kelas = [
        int(kelas)
        for kelas in np.unique(data.target)
        if int(np.count_nonzero(data.target == kelas)) >= 4
    ][:16]
    wajah_per_id: dict[str, list[np.ndarray]] = {}
    for urutan, kelas_id in enumerate(kelas, start=1):
        id_anonim = f"P{urutan:02d}"
        indeks = np.flatnonzero(data.target == kelas_id)[:8]
        wajah_per_id[id_anonim] = [np.asarray(data.images[int(i)], dtype=np.float32) for i in indeks]

    split = bagi_peserta(
        list(wajah_per_id),
        float(konfigurasi["rasio_validasi"]),
        int(konfigurasi["seed_acak"]),
    )
    model_list = list(konfigurasi["model"])
    baris: list[dict[str, Any]] = []
    latensi: dict[str, list[float]] = {}

    for model_index, model in enumerate(model_list):
        generator = np.random.default_rng(int(konfigurasi["seed_acak"]) + model_index)
        proyeksi = generator.normal(0, 1, size=(768, 512)).astype(np.float32)
        fitur: dict[tuple[str, int], np.ndarray] = {}
        template: dict[str, np.ndarray] = {}

        for id_peserta, gambar_list in wajah_per_id.items():
            vector = []
            for nomor, gambar in enumerate(gambar_list):
                kecil = Image.fromarray(np.uint8(np.clip(gambar * 255.0, 0, 255))).resize((32, 24))
                piksel = np.asarray(kecil, dtype=np.float32).reshape(-1) / 255.0
                fitur_gambar = (piksel - float(np.mean(piksel))) / (float(np.std(piksel)) + 1e-6)
                fitur[(id_peserta, nomor)] = fitur_gambar @ proyeksi
                vector.append(fitur[(id_peserta, nomor)])
            referensi = np.mean(vector[:2], axis=0)
            template[id_peserta] = referensi / np.linalg.norm(referensi)

        for id_peserta, gambar_list in wajah_per_id.items():
            targets = [
                target
                for target in wajah_per_id
                if target != id_peserta and split[target] == split[id_peserta]
            ]
            random.Random(f"{konfigurasi['seed_acak']}:{id_peserta}").shuffle(targets)
            targets = targets[: int(konfigurasi["target_impostor_minimum"])]
            for nomor in range(2, len(gambar_list)):
                probe = fitur[(id_peserta, nomor)]
                probe = probe / np.linalg.norm(probe)
                id_pasangan = f"{id_peserta}:demo:{nomor}"
                for jenis, target in [("genuine", id_peserta)] + [("impostor", id) for id in targets]:
                    mulai = time.perf_counter()
                    skor = _cosine(probe, template[target])
                    waktu_ms = (time.perf_counter() - mulai) * 1000
                    baris.append(
                        {
                            "model": model,
                            "split": split[id_peserta],
                            "jenis": jenis,
                            "probe_id": id_peserta,
                            "target_id": target,
                            "kondisi": "demo_lfw",
                            "pasangan_id": f"{id_pasangan}:{target}",
                            "skor": skor,
                            "wajah_ditemukan": True,
                            "processing_ms": waktu_ms,
                        }
                    )
        latensi[model] = [float(baris_demo["processing_ms"]) for baris_demo in baris if baris_demo["model"] == model]

    return baris, {
        "latensi": latensi,
        "split": split,
        "mode": "DEMO, BUKAN DATA PENELITIAN",
        "catatan": (
            "LFW adalah dataset publik. Vektor demonstrasi berasal dari proyeksi piksel "
            "deterministik, bukan ekstraksi ArcFace, FaceNet, atau SFace."
        ),
    }


def _bobot_baris(baris: dict[str, Any], bobot: Counter[str]) -> int:
    pengali = bobot[str(baris["probe_id"])]
    if baris["jenis"] == "impostor":
        pengali *= bobot[str(baris["target_id"])]
    return pengali


def _metrik_berbobot(baris: list[dict[str, Any]], bobot: Counter[str], ambang: float) -> dict[str, float]:
    asli = penyamar = benar = total = 0
    for nilai in baris:
        pengali = _bobot_baris(nilai, bobot)
        total += pengali
        diterima = float(nilai["skor"]) >= ambang
        if nilai["jenis"] == "genuine":
            asli += pengali
            benar += pengali * int(diterima)
        elif nilai["jenis"] == "impostor":
            penyamar += pengali
            benar += pengali * int(not diterima)
    return {
        "far": sum(
            _bobot_baris(nilai, bobot) * int(float(nilai["skor"]) >= ambang)
            for nilai in baris if nilai["jenis"] == "impostor"
        ) / penyamar,
        "frr": sum(
            _bobot_baris(nilai, bobot) * int(float(nilai["skor"]) < ambang)
            for nilai in baris if nilai["jenis"] == "genuine"
        ) / asli,
        "akurasi": benar / total,
    }


def _roc_berbobot(baris: list[dict[str, Any]], bobot: Counter[str]) -> dict[str, float]:
    skor = []
    label = []
    bobot_skor = []
    for nilai in baris:
        if nilai["jenis"] not in ("genuine", "impostor"):
            continue
        jumlah = _bobot_baris(nilai, bobot)
        if jumlah == 0:
            continue
        skor.append(float(nilai["skor"]))
        label.append(1 if nilai["jenis"] == "genuine" else 0)
        bobot_skor.append(jumlah)
    if not label or len(set(label)) != 2:
        raise ValueError("Bootstrap ROC memerlukan sampel genuine dan impostor.")
    false_accept, true_accept, ambang = roc_curve(label, skor, sample_weight=bobot_skor)
    false_reject = 1.0 - true_accept
    indeks = int(np.argmin(np.abs(false_accept - false_reject)))

    return {
        "auc": float(auc(false_accept, true_accept)),
        "eer": float((false_accept[indeks] + false_reject[indeks]) / 2.0),
    }


def _tulis_lingkungan(jalur: Path, seed: int) -> None:
    hasil = [
        f"seed={seed}",
        f"os={platform.platform()}",
        f"python={sys.version}",
        f"cpu={platform.processor()}",
        f"logical_cpu={os.cpu_count()}",
    ]
    if sys.platform == "win32":
        hasil.append("cpu_mode=CPU (layanan wajah harus disiapkan tanpa CUDA)")
    for nama, jalur_model in (
        ("YuNet", MODEL_YUNET),
        ("SFace", MODEL_SFACE),
        ("ArcFace", MODEL_ARCFACE),
    ):
        if jalur_model.is_file():
            with jalur_model.open("rb") as berkas_model:
                hash_model = hashlib.file_digest(berkas_model, "sha256").hexdigest()
            hasil.append(f"model_sha256_{nama}={hash_model}")
        else:
            hasil.append(f"model_sha256_{nama}=tidak_ditemukan")
    hasil.append("pip_freeze:")
    freeze = subprocess.run(
        [sys.executable, "-m", "pip", "freeze"],
        capture_output=True,
        text=True,
        check=False,
    )
    if freeze.returncode != 0:
        raise RuntimeError(f"pip freeze gagal saat merekam lingkungan: {freeze.stderr.strip()}")
    hasil.append(freeze.stdout.strip())
    jalur.write_text("\n".join(hasil) + "\n", encoding="utf-8")


def _gambar_hasil(
    hasil_model: dict[str, dict[str, Any]],
    baris: list[dict[str, Any]],
    jalur_hasil: Path,
) -> None:
    import matplotlib

    matplotlib.use("Agg")
    import matplotlib.pyplot as plt
    from sklearn.metrics import det_curve

    fig, ax = plt.subplots(figsize=(7, 6))
    for model, ringkasan in hasil_model.items():
        roc = ringkasan["roc"]
        ax.plot(roc["far"], roc["tar"], label=f"{model} (AUC={roc['auc']:.3f})")
    ax.plot([0, 1], [0, 1], linestyle="--", color="gray")
    ax.set(xlabel="False Acceptance Rate", ylabel="True Acceptance Rate", title="Kurva ROC")
    ax.legend()
    fig.tight_layout()
    fig.savefig(jalur_hasil / "kurva_roc.png", dpi=300)
    plt.close(fig)

    fig, ax = plt.subplots(figsize=(7, 6))
    for model, ringkasan in hasil_model.items():
        skor = np.asarray(ringkasan["full_genuine"] + ringkasan["full_impostor"], dtype=float)
        label = np.concatenate((
            np.ones(len(ringkasan["full_genuine"]), dtype=np.uint8),
            np.zeros(len(ringkasan["full_impostor"]), dtype=np.uint8),
        ))
        fpr, fnr, _ = det_curve(label, skor)
        ax.plot(np.maximum(fpr, 1e-6), np.maximum(fnr, 1e-6), label=model)
    ax.set_xscale("log")
    ax.set_yscale("log")
    ax.set(xlabel="FAR", ylabel="FRR", title="Kurva DET")
    ax.legend()
    fig.tight_layout()
    fig.savefig(jalur_hasil / "kurva_det.png", dpi=300)
    plt.close(fig)

    kondisi = sorted({str(nilai["kondisi"]) for nilai in baris if nilai["jenis"] == "genuine"})
    fig, ax = plt.subplots(figsize=(9, 5))
    for model in hasil_model:
        nilai_frr = [
            hasil_model[model]["frr_kondisi"].get(kondisi_item, float("nan"))
            for kondisi_item in kondisi
        ]
        ax.plot(kondisi, nilai_frr, marker="o", label=model)
    ax.set(ylabel="False Rejection Rate", title="FRR per kondisi (split uji)")
    ax.tick_params(axis="x", rotation=30)
    ax.legend()
    fig.tight_layout()
    fig.savefig(jalur_hasil / "frr_per_kondisi.png", dpi=300)
    plt.close(fig)

    fig, ax = plt.subplots(figsize=(7, 5))
    data_latensi = [hasil_model[model]["latensi_ms"] for model in hasil_model]
    ax.boxplot(data_latensi, tick_labels=list(hasil_model))
    ax.set(ylabel="Waktu proses (ms)", title="Latensi CPU")
    fig.tight_layout()
    fig.savefig(jalur_hasil / "kotak_latensi.png", dpi=300)
    plt.close(fig)


def tulis_hasil(
    baris: list[dict[str, Any]],
    metadata: dict[str, Any],
    konfigurasi: dict[str, Any],
    mode_demo: bool,
) -> list[str]:
    jalur_hasil = AKAR / konfigurasi["jalur_hasil"]
    jalur_hasil.mkdir(parents=True, exist_ok=True)
    jalur_csv = jalur_hasil / "skor_mentah.csv"
    kolom = [
        "model", "split", "jenis", "probe_id", "target_id", "kondisi",
        "pasangan_id", "skor", "wajah_ditemukan", "processing_ms",
    ]
    with jalur_csv.open("w", newline="", encoding="utf-8") as berkas:
        penulis = csv.DictWriter(berkas, fieldnames=kolom)
        penulis.writeheader()
        penulis.writerows({kunci: baris_skor[kunci] for kunci in kolom} for baris_skor in baris)

    hasil_model: dict[str, dict[str, Any]] = {}
    ulangan_bootstrap = int(konfigurasi["bootstrap_ulangan"])
    seed = int(konfigurasi["seed_acak"])
    models = list(konfigurasi["model"])
    for model in models:
        baris_model = [nilai for nilai in baris if nilai["model"] == model]
        genuine_full = [nilai for nilai in baris_model if nilai["jenis"] == "genuine"]
        impostor_full = [nilai for nilai in baris_model if nilai["jenis"] == "impostor"]
        roc = kurva_roc(
            [float(nilai["skor"]) for nilai in genuine_full],
            [float(nilai["skor"]) for nilai in impostor_full],
        )
        validasi = [nilai for nilai in baris_model if nilai["split"] == "validasi"]
        genuine_validasi = [nilai for nilai in validasi if nilai["jenis"] == "genuine"]
        impostor_validasi = [nilai for nilai in validasi if nilai["jenis"] == "impostor"]
        ambang = ambang_eer_validasi(
            [float(nilai["skor"]) for nilai in genuine_validasi],
            [float(nilai["skor"]) for nilai in impostor_validasi],
        )
        uji = [nilai for nilai in baris_model if nilai["split"] == "uji"]
        genuine_uji = [nilai for nilai in uji if nilai["jenis"] == "genuine"]
        impostor_uji = [nilai for nilai in uji if nilai["jenis"] == "impostor"]
        uji_biometrik = [
            nilai for nilai in uji if nilai["jenis"] in ("genuine", "impostor")
        ]
        metrik_uji = hitung_far_frr(
            [float(nilai["skor"]) for nilai in genuine_uji],
            [float(nilai["skor"]) for nilai in impostor_uji],
            ambang,
        )

        def hitung_metrik(nama: str) -> tuple[float, float]:
            def pemilih(baris_bootstrap: list[dict[str, Any]], bobot: Counter[str]) -> float:
                return _metrik_berbobot(baris_bootstrap, bobot, ambang)[nama]
            return bootstrap_peserta(uji_biometrik, pemilih, ulangan_bootstrap, seed)

        ci = {
            nama: hitung_metrik(nama)
            for nama in ("far", "frr", "akurasi")
        }
        data_penuh = genuine_full + impostor_full
        ci["auc"] = bootstrap_peserta(
            data_penuh,
            lambda isi, bobot: _roc_berbobot(isi, bobot)["auc"],
            ulangan_bootstrap,
            seed,
        )
        ci["eer_seluruh_data"] = bootstrap_peserta(
            data_penuh,
            lambda isi, bobot: _roc_berbobot(isi, bobot)["eer"],
            ulangan_bootstrap,
            seed,
        )
        frr_kondisi: dict[str, float] = {}
        for kondisi in sorted({str(nilai["kondisi"]) for nilai in genuine_uji}):
            subset = [nilai for nilai in genuine_uji if nilai["kondisi"] == kondisi]
            frr_kondisi[kondisi] = float(np.mean([float(nilai["skor"]) < ambang for nilai in subset]))

        serangan = {
            jenis.removeprefix("serangan_"): (
                float(np.mean([float(nilai["skor"]) >= ambang for nilai in uji if nilai["jenis"] == jenis]))
                if any(nilai["jenis"] == jenis for nilai in uji)
                else None
            )
            for jenis in ("serangan_cetak", "serangan_layar")
        }
        ukuran_latensi = metadata["latensi"][model]
        hasil_model[model] = {
            "ambang_dari_validasi": ambang,
            "uji": metrik_uji,
            "interval_kepercayaan_95_bootstrap_peserta": ci,
            "roc": roc,
            "frr_kondisi": frr_kondisi,
            "penerimaan_serangan": serangan,
            "latensi_ms": ukuran_latensi,
            "latensi_median_ms": float(statistics.median(ukuran_latensi)),
            "latensi_p95_ms": float(np.percentile(ukuran_latensi, 95)),
            "full_genuine": [float(nilai["skor"]) for nilai in genuine_full],
            "full_impostor": [float(nilai["skor"]) for nilai in impostor_full],
        }

    berpasangan: dict[str, Any] = {}
    daftar_pasangan = [(model, nilai) for model in models for nilai in models if model < nilai]
    for model_a, model_b in daftar_pasangan:
        baris_a = {
            str(nilai["pasangan_id"]): nilai
            for nilai in baris
            if nilai["model"] == model_a and nilai["split"] == "uji"
            and nilai["jenis"] in ("genuine", "impostor")
        }
        baris_b = {
            str(nilai["pasangan_id"]): nilai
            for nilai in baris
            if nilai["model"] == model_b and nilai["split"] == "uji"
            and nilai["jenis"] in ("genuine", "impostor")
        }
        pasangan = sorted(set(baris_a) & set(baris_b))
        keputusan_a = [
            (float(baris_a[id_pasangan]["skor"]) >= float(hasil_model[model_a]["ambang_dari_validasi"]))
            == (baris_a[id_pasangan]["jenis"] == "genuine")
            for id_pasangan in pasangan
        ]
        keputusan_b = [
            (float(baris_b[id_pasangan]["skor"]) >= float(hasil_model[model_b]["ambang_dari_validasi"]))
            == (baris_b[id_pasangan]["jenis"] == "genuine")
            for id_pasangan in pasangan
        ]
        berpasangan[f"{model_a}_vs_{model_b}"] = uji_mcnemar_tepat(keputusan_a, keputusan_b)

    _gambar_hasil(hasil_model, baris, jalur_hasil)
    for model, hasil in hasil_model.items():
        hasil["roc"] = {
            "auc": hasil["roc"]["auc"],
            "eer_seluruh_data": hasil["roc"]["eer"],
            "ambang_eer_seluruh_data": hasil["roc"]["ambang_eer"],
        }

    ringkasan = {
        "label": "DEMO, BUKAN DATA PENELITIAN" if mode_demo else "DATA PENELITIAN",
        "protokol": {
            "seed": seed,
            "rasio_validasi": konfigurasi["rasio_validasi"],
            "target_impostor_minimum": konfigurasi["target_impostor_minimum"],
            "bootstrap_ulangan": ulangan_bootstrap,
            "latensi_ulangan": konfigurasi["latensi_ulangan"],
            "latensi_pemanasan": konfigurasi["pemanasan_latensi"],
        },
        "jumlah_peserta": len(metadata["split"]),
        "jumlah_pasangan": len(baris),
        "split_peserta": metadata["split"],
        "model": {
            model: {
                **{
                    kunci: nilai
                    for kunci, nilai in hasil.items()
                    if kunci not in ("full_genuine", "full_impostor", "latensi_ms")
                },
                "latensi_ms": {
                    "median": hasil["latensi_median_ms"],
                    "p95": hasil["latensi_p95_ms"],
                },
            }
            for model, hasil in hasil_model.items()
        },
        "perbandingan_berpasangan_mcnemar": berpasangan,
        "catatan": metadata.get("catatan"),
    }
    (jalur_hasil / "ringkasan.json").write_text(
        json.dumps(ringkasan, ensure_ascii=False, indent=2, default=_json_default),
        encoding="utf-8",
    )

    field_tabel = [
        "model", "ambang_validasi", "FAR_uji", "FRR_uji", "akurasi_uji",
        "AUC_semua", "EER_semua", "FAR_CI95", "FRR_CI95", "akurasi_CI95", "AUC_CI95", "EER_CI95",
        "serangan_cetak", "serangan_layar", "latensi_median_ms", "latensi_p95_ms",
    ]
    tabel = []
    for model, hasil in hasil_model.items():
        tabel.append(
            {
                "model": model,
                "ambang_validasi": hasil["ambang_dari_validasi"],
                "FAR_uji": hasil["uji"]["far"],
                "FRR_uji": hasil["uji"]["frr"],
                "akurasi_uji": hasil["uji"]["akurasi"],
                "AUC_semua": hasil["roc"]["auc"],
                "EER_semua": hasil["roc"]["eer_seluruh_data"],
                "FAR_CI95": json.dumps(hasil["interval_kepercayaan_95_bootstrap_peserta"]["far"]),
                "FRR_CI95": json.dumps(hasil["interval_kepercayaan_95_bootstrap_peserta"]["frr"]),
                "akurasi_CI95": json.dumps(hasil["interval_kepercayaan_95_bootstrap_peserta"]["akurasi"]),
                "AUC_CI95": json.dumps(hasil["interval_kepercayaan_95_bootstrap_peserta"]["auc"]),
                "EER_CI95": json.dumps(hasil["interval_kepercayaan_95_bootstrap_peserta"]["eer_seluruh_data"]),
                "serangan_cetak": hasil["penerimaan_serangan"]["cetak"],
                "serangan_layar": hasil["penerimaan_serangan"]["layar"],
                "latensi_median_ms": hasil["latensi_median_ms"],
                "latensi_p95_ms": hasil["latensi_p95_ms"],
            }
        )
    with (jalur_hasil / "tabel_hasil.csv").open("w", newline="", encoding="utf-8") as berkas:
        penulis = csv.DictWriter(berkas, fieldnames=field_tabel)
        penulis.writeheader()
        penulis.writerows(tabel)
    markdown = ["| " + " | ".join(field_tabel) + " |", "|" + "|".join(["---"] * len(field_tabel)) + "|"]
    for isi_tabel in tabel:
        markdown.append("| " + " | ".join(str(isi_tabel[kolom]) for kolom in field_tabel) + " |")
    (jalur_hasil / "tabel_hasil.md").write_text("\n".join(markdown) + "\n", encoding="utf-8")
    _tulis_lingkungan(jalur_hasil / "lingkungan.txt", seed)

    return sorted(berkas.name for berkas in jalur_hasil.iterdir() if berkas.is_file())


def _json_default(nilai: Any) -> Any:
    if isinstance(nilai, np.ndarray):
        return nilai.tolist()
    if isinstance(nilai, (np.integer, np.floating)):
        return nilai.item()
    raise TypeError(f"Jenis tidak dapat diubah menjadi JSON: {type(nilai).__name__}")


def main() -> int:
    parser = argparse.ArgumentParser(description="Evaluasi komparatif model pengenalan wajah.")
    parser.add_argument("--demo", action="store_true", help="Gunakan subset publik LFW, bukan data penelitian.")
    argumen = parser.parse_args()
    konfigurasi = baca_konfigurasi()

    if argumen.demo:
        baris, metadata = demo_lfw(konfigurasi)
    else:
        jalur_data = AKAR / konfigurasi["jalur_data"]
        masalah = __import__(
            "evaluasi_wajah.periksa_struktur_data",
            fromlist=["periksa_struktur"],
        ).periksa_struktur(jalur_data, konfigurasi)
        if masalah:
            print("\n".join(f"- {pesan}" for pesan in masalah))
            return 1
        baris, metadata = evaluasi_korpus(konfigurasi)

    berkas = tulis_hasil(baris, metadata, konfigurasi, argumen.demo)
    print("Hasil:", ", ".join(str(Path(konfigurasi["jalur_hasil"]) / nama) for nama in berkas))
    if argumen.demo:
        print("DEMO, BUKAN DATA PENELITIAN")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
