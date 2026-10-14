# ==========================================
# PEMBAHASAN / BAGIAN: Uji API Kesehatan, Kunci, Dimensi, dan Jumlah Wajah
# ==========================================
from __future__ import annotations

import base64

import cv2
import numpy as np
import pytest
from fastapi.testclient import TestClient

import layanan_wajah.app as api
from layanan_wajah.mesin import MesinWajah


class DetektorPalsu:
    def __init__(self, jumlah_wajah: int) -> None:
        self.jumlah_wajah = jumlah_wajah

    def deteksi(self, gambar: np.ndarray) -> list[dict[str, np.ndarray | float]]:
        return [
            {
                "kotak": np.array([15, 15, 60, 60], dtype=np.float32),
                "landmark": np.array(
                    [[28, 35], [56, 35], [42, 48], [31, 59], [53, 59]],
                    dtype=np.float32,
                ),
                "keyakinan": 0.99,
            }
            for _ in range(self.jumlah_wajah)
        ]


class PenyediaPalsu:
    def __init__(self, dimensi: int) -> None:
        self.dimensi = dimensi

    def hasilkan(self, gambar: np.ndarray, landmark: np.ndarray) -> np.ndarray:
        embedding = np.ones(self.dimensi, dtype=np.float32)
        return embedding / np.linalg.norm(embedding)


def buat_gambar() -> bytes:
    gambar = np.full((100, 100, 3), 180, dtype=np.uint8)
    berhasil, isi = cv2.imencode(".jpg", gambar)
    assert berhasil
    return isi.tobytes()


@pytest.fixture
def klien(monkeypatch: pytest.MonkeyPatch) -> TestClient:
    monkeypatch.setattr(api, "KUNCI_LAYANAN", "kunci-uji")
    return TestClient(api.app)


def pasang_mesin(
    monkeypatch: pytest.MonkeyPatch,
    jumlah_wajah: int,
) -> None:
    mesin = MesinWajah(DetektorPalsu(jumlah_wajah))
    monkeypatch.setattr(mesin, "_ambil_penyedia", lambda nama: PenyediaPalsu(
        {"ArcFace": 512, "FaceNet": 512, "SFace": 128}[nama or "FaceNet"]
    ))
    monkeypatch.setattr(api, "mesin", mesin)


def test_kesehatan_memastikan_layanan_stateless_dengan_kunci(
    klien: TestClient,
) -> None:
    respons = klien.get("/v1/kesehatan", headers={"X-KUNCI-LAYANAN": "kunci-uji"})

    assert respons.status_code == 200
    assert respons.json()["stateless"] is True
    assert set(respons.json()["model_didukung"]) == {"ArcFace", "FaceNet", "SFace"}


@pytest.mark.parametrize(
    ("nama_model", "dimensi"),
    [("ArcFace", 512), ("FaceNet", 512), ("SFace", 128)],
)
def test_embedding_mengembalikan_dimensi_terdefinisi_tiap_model(
    klien: TestClient,
    monkeypatch: pytest.MonkeyPatch,
    nama_model: str,
    dimensi: int,
) -> None:
    pasang_mesin(monkeypatch, 1)
    respons = klien.post(
        "/v1/embedding",
        headers={"X-KUNCI-LAYANAN": "kunci-uji"},
        files={"foto": ("anonim.jpg", buat_gambar(), "image/jpeg")},
        data={"model": nama_model},
    )

    assert respons.status_code == 200
    assert respons.json()["embedding_dimension"] == dimensi
    assert len(base64.b64decode(respons.json()["embedding_base64"])) == dimensi * 4
    assert respons.json()["face_count"] == 1


def test_endpoint_menolak_permohonan_tanpa_kunci_api(
    klien: TestClient,
) -> None:
    respons = klien.post(
        "/v1/embedding",
        files={"foto": ("anonim.jpg", buat_gambar(), "image/jpeg")},
    )

    assert respons.status_code == 401


@pytest.mark.parametrize("jumlah_wajah", [0, 2])
def test_embedding_menandai_foto_tanpa_tepat_satu_wajah(
    klien: TestClient,
    monkeypatch: pytest.MonkeyPatch,
    jumlah_wajah: int,
) -> None:
    pasang_mesin(monkeypatch, jumlah_wajah)
    respons = klien.post(
        "/v1/embedding",
        headers={"X-KUNCI-LAYANAN": "kunci-uji"},
        files={"foto": ("anonim.jpg", buat_gambar(), "image/jpeg")},
    )

    assert respons.status_code == 200
    assert respons.json()["face_count"] == jumlah_wajah
    assert respons.json()["face_found"] is False
    assert respons.json()["embedding_base64"] is None


def test_verifikasi_menghitung_cosine_similarity_dan_tidak_menyimpan_foto(
    klien: TestClient,
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    pasang_mesin(monkeypatch, 1)
    referensi = np.ones(512, dtype="<f4")
    referensi /= np.linalg.norm(referensi)
    respons = klien.post(
        "/v1/verifikasi",
        headers={"X-KUNCI-LAYANAN": "kunci-uji"},
        files={"foto": ("anonim.jpg", buat_gambar(), "image/jpeg")},
        data={
            "model": "FaceNet",
            "referensi_base64": base64.b64encode(referensi.tobytes()).decode("ascii"),
        },
    )

    assert respons.status_code == 200
    assert respons.json()["score"] == pytest.approx(1.0)
    assert "embedding_base64" not in respons.json()
