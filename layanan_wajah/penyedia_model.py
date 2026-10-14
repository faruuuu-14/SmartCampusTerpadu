# ==========================================
# PEMBAHASAN / BAGIAN: Antarmuka dan Adapter Model Wajah
# ==========================================
from __future__ import annotations

from abc import ABC, abstractmethod

import cv2
import numpy as np

from layanan_wajah.pengaturan import MODEL_ARCFACE, MODEL_SFACE
from layanan_wajah.penyelarasan import selaraskan_wajah


class PenyediaModel(ABC):
    nama: str
    dimensi: int
    ukuran: int

    @abstractmethod
    def hasilkan(self, gambar: np.ndarray, landmark: np.ndarray) -> np.ndarray:
        raise NotImplementedError

    @staticmethod
    def normalisasi(embedding: np.ndarray) -> np.ndarray:
        nilai = np.asarray(embedding, dtype=np.float32).reshape(-1)
        norma = float(np.linalg.norm(nilai))

        if not np.isfinite(norma) or norma == 0:
            raise RuntimeError("Model menghasilkan embedding yang tidak valid.")

        return nilai / norma


class PenyediaArcFace(PenyediaModel):
    nama = "ArcFace"
    dimensi = 512
    ukuran = 112

    def __init__(self) -> None:
        try:
            from insightface.model_zoo import get_model

            if not MODEL_ARCFACE.is_file():
                raise RuntimeError(f"Bobot ArcFace tidak ditemukan: {MODEL_ARCFACE}")
            self._model = get_model(str(MODEL_ARCFACE))
            if self._model is None:
                raise RuntimeError("InsightFace tidak menemukan bobot ArcFace.")
            self._model.prepare(ctx_id=-1)
        except Exception as kesalahan:
            raise RuntimeError(
                "ArcFace/InsightFace gagal dimuat. Pastikan bobot w600k_r50 tersedia "
                "dan paket kompatibel dengan Python serta Windows ini."
            ) from kesalahan

    def hasilkan(self, gambar: np.ndarray, landmark: np.ndarray) -> np.ndarray:
        wajah = selaraskan_wajah(gambar, landmark, self.ukuran)
        return self.normalisasi(self._model.get_feat(wajah))


class PenyediaFaceNet(PenyediaModel):
    nama = "FaceNet"
    dimensi = 512
    ukuran = 160

    def __init__(self) -> None:
        try:
            import torch
            from facenet_pytorch import InceptionResnetV1

            self._torch = torch
            self._torch.set_num_threads(1)
            self._model = InceptionResnetV1(pretrained="vggface2").eval().cpu()
        except Exception as kesalahan:
            raise RuntimeError(
                "FaceNet/facenet-pytorch gagal dimuat. Periksa dukungan PyTorch CPU "
                "dan ketersediaan bobot VGGFace2."
            ) from kesalahan

    def hasilkan(self, gambar: np.ndarray, landmark: np.ndarray) -> np.ndarray:
        wajah = selaraskan_wajah(gambar, landmark, self.ukuran)
        wajah_rgb = cv2.cvtColor(wajah, cv2.COLOR_BGR2RGB)
        tensor = self._torch.from_numpy(wajah_rgb).permute(2, 0, 1).float()
        tensor = ((tensor / 255.0) - 0.5) / 0.5

        with self._torch.inference_mode():
            embedding = self._model(tensor.unsqueeze(0)).squeeze(0).numpy()

        return self.normalisasi(embedding)


class PenyediaSFace(PenyediaModel):
    nama = "SFace"
    dimensi = 128
    ukuran = 112

    def __init__(self) -> None:
        if not MODEL_SFACE.is_file():
            raise RuntimeError(
                f"Bobot SFace tidak ditemukan: {MODEL_SFACE}. "
                "Unduh model sesuai petunjuk README layanan_wajah."
            )

        self._model = cv2.FaceRecognizerSF.create(str(MODEL_SFACE), "")

    def hasilkan(self, gambar: np.ndarray, landmark: np.ndarray) -> np.ndarray:
        wajah = selaraskan_wajah(gambar, landmark, self.ukuran)
        embedding = self._model.feature(wajah)
        return self.normalisasi(embedding)


def buat_penyedia(nama_model: str) -> PenyediaModel:
    penyedia = {
        "ArcFace": PenyediaArcFace,
        "FaceNet": PenyediaFaceNet,
        "SFace": PenyediaSFace,
    }

    if nama_model not in penyedia:
        raise ValueError("Model harus ArcFace, FaceNet, atau SFace.")

    return penyedia[nama_model]()
