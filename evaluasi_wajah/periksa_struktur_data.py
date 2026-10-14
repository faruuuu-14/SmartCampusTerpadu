# ==========================================
# PEMBAHASAN / BAGIAN: Pemeriksaan Struktur Dataset Wajah Anonim
# ==========================================
from __future__ import annotations

import argparse
import re
from pathlib import Path
from typing import Any

import yaml
from PIL import Image, UnidentifiedImageError

KONDISI = ("referensi", "terang", "redup", "sudut", "aksesori", "jarak")
JENIS_SERANGAN = ("cetak", "layar")
POLA_ID = re.compile(r"^P\d{2,}$")


def periksa_struktur(jalur_data: Path, konfigurasi: dict[str, Any]) -> list[str]:
    masalah: list[str] = []
    ekstensi = {".jpg", ".jpeg"}
    peserta = sorted(
        jalur
        for jalur in jalur_data.iterdir()
        if jalur.is_dir() and not jalur.name.startswith("_")
    ) if jalur_data.is_dir() else []
    minimum_peserta = int(konfigurasi.get("jumlah_peserta_minimum", 8))

    if len(peserta) < minimum_peserta:
        masalah.append(f"Jumlah peserta {len(peserta)} di bawah batas minimum {minimum_peserta}.")

    id_peserta = {direktori.name for direktori in peserta}

    for direktori in peserta:
        if not POLA_ID.fullmatch(direktori.name):
            masalah.append(f"ID peserta harus anonim berformat P01, P02, ...: {direktori.name}")
        for kondisi in KONDISI:
            berkas = sorted(
                berkas
                for berkas in (direktori / kondisi).glob("*")
                if berkas.is_file() and berkas.suffix.lower() in ekstensi
            ) if (direktori / kondisi).is_dir() else []
            batas = int(konfigurasi.get("foto_minimum", {}).get(kondisi, 1))

            if len(berkas) < batas:
                masalah.append(
                    f"{direktori.name}/{kondisi}: perlu minimal {batas} foto JPG, ditemukan {len(berkas)}."
                )
            masalah.extend(_periksa_foto(berkas))

    for jenis in JENIS_SERANGAN:
        dasar_serangan = jalur_data / "_serangan" / jenis
        if not dasar_serangan.is_dir():
            masalah.append(f"Folder serangan wajib tidak ditemukan: _serangan/{jenis}")
            continue

        for direktori in sorted(dasar_serangan.iterdir()):
            if not direktori.is_dir() or not POLA_ID.fullmatch(direktori.name):
                masalah.append(f"ID serangan harus anonim berformat P01, P02, ...: {direktori}")
                continue
            if direktori.name not in id_peserta:
                masalah.append(f"ID serangan tidak ditemukan pada dataset peserta: {direktori.name}")
            berkas = [
                berkas
                for berkas in direktori.glob("*")
                if berkas.is_file() and berkas.suffix.lower() in ekstensi
            ]
            minimum_foto = int(konfigurasi.get("foto_serangan_minimum", 1))
            if len(berkas) < minimum_foto:
                masalah.append(
                    f"{direktori.relative_to(jalur_data)}: perlu minimal {minimum_foto} foto serangan JPG, "
                    f"ditemukan {len(berkas)}."
                )
            masalah.extend(_periksa_foto(berkas))

    return masalah


def _periksa_foto(berkas_foto: list[Path]) -> list[str]:
    masalah: list[str] = []
    for berkas in berkas_foto:
        try:
            with Image.open(berkas) as gambar:
                gambar.verify()
        except (OSError, UnidentifiedImageError):
            masalah.append(f"Foto rusak atau bukan gambar: {berkas}")
    return masalah


def main() -> int:
    parser = argparse.ArgumentParser(description="Validasi struktur dataset biometrik anonim.")
    parser.add_argument("--data", default="evaluasi_wajah/data_wajah")
    parser.add_argument("--konfigurasi", default="evaluasi_wajah/konfigurasi.yaml")
    argumen = parser.parse_args()
    with Path(argumen.konfigurasi).open(encoding="utf-8") as berkas:
        konfigurasi = yaml.safe_load(berkas)
    masalah = periksa_struktur(Path(argumen.data), konfigurasi)

    if masalah:
        print("\n".join(f"- {pesan}" for pesan in masalah))
        return 1

    print("Struktur data valid. Identitas harus tetap anonim.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
