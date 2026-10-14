# ==========================================
# PEMBAHASAN / BAGIAN: Pengunduhan Bobot Terbuka YuNet dan SFace
# ==========================================
from __future__ import annotations

import hashlib
import shutil
import time
from urllib.error import URLError
from urllib.request import urlopen
from pathlib import Path

import cv2

from layanan_wajah.pengaturan import MODEL_SFACE, MODEL_YUNET

SUMBER = {
    MODEL_YUNET: (
        "https://github.com/opencv/opencv_zoo/raw/refs/heads/main/models/"
        "face_detection_yunet/face_detection_yunet_2023mar.onnx",
        "8f2383e4dd3cfbb4553ea8718107fc0423210dc964f9f4280604804ed2552fa4",
    ),
    MODEL_SFACE: (
        "https://github.com/opencv/opencv_zoo/raw/refs/heads/main/models/"
        "face_recognition_sface/face_recognition_sface_2021dec.onnx",
        "0ba9fbfa01b5270c96627c4ef784da859931e02f04419c829e83484087c34e79",
    ),
}


def unduh_model() -> None:
    for jalur, (url, hash_diharapkan) in SUMBER.items():
        jalur.parent.mkdir(parents=True, exist_ok=True)
        if jalur.is_file():
            hash_aktual = hashlib.sha256(jalur.read_bytes()).hexdigest()
        else:
            sementara = jalur.with_suffix(jalur.suffix + ".unduh")
            try:
                for percobaan in range(3):
                    try:
                        with urlopen(url, timeout=60) as respons, sementara.open("wb") as tujuan:
                            shutil.copyfileobj(respons, tujuan)
                        hash_aktual = hashlib.sha256(sementara.read_bytes()).hexdigest()
                        if hash_aktual != hash_diharapkan:
                            raise RuntimeError(f"Hash bobot tidak cocok: {jalur.name}")
                        sementara.replace(jalur)
                        break
                    except (URLError, TimeoutError, ConnectionError):
                        sementara.unlink(missing_ok=True)
                        if percobaan == 2:
                            raise
                        time.sleep(2**percobaan)
            finally:
                sementara.unlink(missing_ok=True)
        if hash_aktual != hash_diharapkan:
            raise RuntimeError(f"Hash bobot tidak cocok: {jalur.name}")
        print(f"{jalur.name}: SHA-256 {hash_aktual}")


if __name__ == "__main__":
    unduh_model()
