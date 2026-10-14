# ==========================================
# PEMBAHASAN / BAGIAN: Uji Metrik FAR, FRR, EER, ROC, dan Bootstrap
# ==========================================
from collections import Counter

import pytest

from evaluasi_wajah.metrik import (
    ambang_eer_validasi,
    bootstrap_peserta,
    hitung_far_frr,
    kurva_roc,
    uji_mcnemar_tepat,
)


def test_far_frr_akurasi_dan_eer_diketahui() -> None:
    asli = [0.9, 0.8, 0.7, 0.6]
    penyamar = [0.1, 0.2, 0.3, 0.4]
    ambang = ambang_eer_validasi(asli, penyamar)
    hasil = hitung_far_frr(asli, penyamar, ambang)
    roc = kurva_roc(asli, penyamar)

    assert hasil == {"far": 0.0, "frr": 0.0, "akurasi": 1.0}
    assert roc["auc"] == pytest.approx(1.0)
    assert roc["eer"] == pytest.approx(0.0)


def test_far_dan_frr_menghitung_salah_terima_dan_tolak() -> None:
    hasil = hitung_far_frr([0.2, 0.8], [0.1, 0.9], ambang=0.5)

    assert hasil == {"far": 0.5, "frr": 0.5, "akurasi": 0.5}


def test_bootstrap_mengambil_ulang_peserta_dengan_seed_tetap() -> None:
    baris = [
        {"probe_id": "P01", "target_id": "P01", "jenis": "genuine", "benar": 1},
        {"probe_id": "P02", "target_id": "P02", "jenis": "genuine", "benar": 0},
        {"probe_id": "P01", "target_id": "P02", "jenis": "impostor", "benar": 1},
        {"probe_id": "P02", "target_id": "P01", "jenis": "impostor", "benar": 1},
    ]

    def akurasi_berbobot(isi: list[dict[str, object]], bobot: Counter[str]) -> float:
        nilai = [
            (float(baris_uji["benar"]), bobot[str(baris_uji["probe_id"])] *
             (bobot[str(baris_uji["target_id"])] if baris_uji["jenis"] == "impostor" else 1))
            for baris_uji in isi
        ]
        total = sum(bobot_untuk_baris for _, bobot_untuk_baris in nilai)
        return sum(benar * bobot_untuk_baris for benar, bobot_untuk_baris in nilai) / total

    pertama = bootstrap_peserta(baris, akurasi_berbobot, 200, seed=7)
    kedua = bootstrap_peserta(baris, akurasi_berbobot, 200, seed=7)

    assert pertama == kedua
    assert 0 <= pertama[0] <= pertama[1] <= 1


def test_mcnemar_membandingkan_keputusan_pada_pasangan_yang_sama() -> None:
    hasil = uji_mcnemar_tepat([True, True, False, False], [True, False, False, True])

    assert hasil["discordant_pertama"] == 1
    assert hasil["discordant_kedua"] == 1
    assert hasil["p_value"] == 1.0


def test_mcnemar_menggunakan_jumlah_pasangan_tidak_selaras_yang_benar() -> None:
    hasil = uji_mcnemar_tepat([True] * 4 + [False] * 4, [False] * 4 + [False] * 4)

    assert hasil["discordant_pertama"] == 4
    assert hasil["discordant_kedua"] == 0
    assert hasil["p_value"] == pytest.approx(0.125)
