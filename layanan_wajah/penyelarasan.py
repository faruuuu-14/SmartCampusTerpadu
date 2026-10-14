# ==========================================
# PEMBAHASAN / BAGIAN: Penyelarasan Landmark Wajah Bersama
# ==========================================
from __future__ import annotations

import cv2
import numpy as np

LANDMARK_REFERENSI_112 = np.array(
    [
        [38.2946, 51.6963],
        [73.5318, 51.5014],
        [56.0252, 71.7366],
        [41.5493, 92.3655],
        [70.7299, 92.2041],
    ],
    dtype=np.float32,
)


def selaraskan_wajah(gambar: np.ndarray, landmark: np.ndarray, ukuran: int) -> np.ndarray:
    titik_tujuan = LANDMARK_REFERENSI_112 * (ukuran / 112.0)
    matriks, _ = cv2.estimateAffinePartial2D(
        landmark.astype(np.float32),
        titik_tujuan,
        method=cv2.LMEDS,
    )

    if matriks is None:
        raise ValueError("Landmark wajah tidak dapat disejajarkan.")

    return cv2.warpAffine(
        gambar,
        matriks,
        (ukuran, ukuran),
        flags=cv2.INTER_LINEAR,
        borderMode=cv2.BORDER_CONSTANT,
        borderValue=(0, 0, 0),
    )
