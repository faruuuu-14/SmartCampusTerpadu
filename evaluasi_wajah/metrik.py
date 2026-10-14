# ==========================================
# PEMBAHASAN / BAGIAN: Metrik Biometrik dan Bootstrap Berkelompok
# ==========================================
from __future__ import annotations

from collections import Counter
from typing import Callable, Iterable

import numpy as np
from scipy.stats import binomtest
from sklearn.metrics import auc, roc_curve


def hitung_far_frr(
    skor_asli: Iterable[float],
    skor_penyamar: Iterable[float],
    ambang: float,
) -> dict[str, float]:
    asli = np.asarray(list(skor_asli), dtype=float)
    penyamar = np.asarray(list(skor_penyamar), dtype=float)

    if asli.size == 0 or penyamar.size == 0:
        raise ValueError("Skor genuine dan impostor harus sama-sama tersedia.")

    far = float(np.mean(penyamar >= ambang))
    frr = float(np.mean(asli < ambang))
    benar = int(np.count_nonzero(asli >= ambang) + np.count_nonzero(penyamar < ambang))

    return {
        "far": far,
        "frr": frr,
        "akurasi": benar / int(asli.size + penyamar.size),
    }


def kurva_roc(skor_asli: Iterable[float], skor_penyamar: Iterable[float]) -> dict[str, object]:
    asli = np.asarray(list(skor_asli), dtype=float)
    penyamar = np.asarray(list(skor_penyamar), dtype=float)

    if asli.size == 0 or penyamar.size == 0:
        raise ValueError("Kurva ROC memerlukan skor genuine dan impostor.")

    label = np.concatenate((np.ones(asli.size), np.zeros(penyamar.size)))
    skor = np.concatenate((asli, penyamar))
    far, tar, ambang = roc_curve(label, skor)
    fnr = 1.0 - tar
    indeks = int(np.argmin(np.abs(far - fnr)))
    eer = float((far[indeks] + fnr[indeks]) / 2.0)

    return {
        "far": far,
        "tar": tar,
        "frr": fnr,
        "ambang": ambang,
        "auc": float(auc(far, tar)),
        "eer": eer,
        "ambang_eer": float(ambang[indeks]),
    }


def ambang_eer_validasi(skor_asli: Iterable[float], skor_penyamar: Iterable[float]) -> float:
    return float(kurva_roc(skor_asli, skor_penyamar)["ambang_eer"])


def bootstrap_peserta(
    baris: list[dict[str, object]],
    fungsi_metrik: Callable[[list[dict[str, object]], Counter[str]], float],
    jumlah_ulangan: int,
    seed: int,
) -> tuple[float, float]:
    peserta = sorted(
        {
            str(baris_baris["probe_id"])
            for baris_baris in baris
        }
        | {
            str(baris_baris["target_id"])
            for baris_baris in baris
            if baris_baris["jenis"] == "impostor"
        }
    )

    if not peserta or jumlah_ulangan < 1:
        raise ValueError("Bootstrap memerlukan peserta dan minimal satu ulangan.")

    generator = np.random.default_rng(seed)
    nilai = []

    for _ in range(jumlah_ulangan):
        hasil_sampel = generator.choice(peserta, size=len(peserta), replace=True)
        while len(set(hasil_sampel)) < 2 and len(peserta) > 1:
            hasil_sampel = generator.choice(peserta, size=len(peserta), replace=True)
        bobot = Counter(str(nama) for nama in hasil_sampel)
        nilai.append(float(fungsi_metrik(baris, bobot)))

    return tuple(float(nilai_urut) for nilai_urut in np.quantile(nilai, [0.025, 0.975]))


def uji_mcnemar_tepat(
    keputusan_pertama: Iterable[bool],
    keputusan_kedua: Iterable[bool],
) -> dict[str, float | int]:
    pertama = np.asarray(list(keputusan_pertama), dtype=bool)
    kedua = np.asarray(list(keputusan_kedua), dtype=bool)

    if pertama.size == 0 or pertama.size != kedua.size:
        raise ValueError("Keputusan berpasangan harus berukuran sama dan tidak kosong.")

    discordant_pertama = int(np.count_nonzero(pertama & ~kedua))
    discordant_kedua = int(np.count_nonzero(~pertama & kedua))
    discordant = discordant_pertama + discordant_kedua
    return {
        "discordant_pertama": discordant_pertama,
        "discordant_kedua": discordant_kedua,
        "p_value": float(binomtest(discordant_pertama, n=discordant, p=0.5).pvalue)
        if discordant
        else 1.0,
    }
