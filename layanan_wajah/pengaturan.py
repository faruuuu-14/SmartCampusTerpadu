# ==========================================
# PEMBAHASAN / BAGIAN: Pengaturan Layanan Wajah
# ==========================================
from __future__ import annotations

import os
from pathlib import Path

from dotenv import load_dotenv

AKAR_PROYEK = Path(__file__).resolve().parent.parent
load_dotenv(AKAR_PROYEK / ".env")
load_dotenv(Path(__file__).resolve().parent / ".env")

KUNCI_LAYANAN = os.getenv("WAJAH_KUNCI", "")
MODEL_AKTIF = os.getenv("MODEL_AKTIF", "FaceNet")
MODEL_YUNET = Path(
    os.getenv(
        "MODEL_YUNET",
        str(Path(__file__).resolve().parent / "models" / "face_detection_yunet_2023mar.onnx"),
    )
)
MODEL_SFACE = Path(
    os.getenv(
        "MODEL_SFACE",
        str(Path(__file__).resolve().parent / "models" / "face_recognition_sface_2021dec.onnx"),
    )
)
MODEL_ARCFACE = Path(
    os.getenv(
        "MODEL_ARCFACE",
        str(Path(__file__).resolve().parent / "models" / "arcface_w600k_r50.onnx"),
    )
)
