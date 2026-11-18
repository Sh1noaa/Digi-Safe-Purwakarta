import { parseGIF, decompressFrames } from 'gifuct-js';
import { GIFEncoder, quantize, applyPalette } from 'gifenc';

/**
 * Hitung koordinat persegi crop pada gambar asli berdasarkan posisi & zoom viewport.
 */
function computeCropRect(state, targetW = null, targetH = null) {
    const natW = targetW || state.natW;
    const natH = targetH || state.natH;
    const scaleX = natW / state.natW;
    const scaleY = natH / state.natH;

    const baseScale = Math.max(state.vpW / state.natW, state.vpH / state.natH);
    const scale = baseScale * state.zoom;
    const renW = state.natW * scale;
    const renH = state.natH * scale;
    const imgLeft = (state.vpW - renW) / 2 + state.panX;
    const imgTop = (state.vpH - renH) / 2 + state.panY;

    const sx = Math.max(0, (-imgLeft / scale) * scaleX);
    const sy = Math.max(0, (-imgTop / scale) * scaleY);
    const sw = Math.min(natW - sx, (state.vpW / scale) * scaleX);
    const sh = Math.min(natH - sy, (state.vpH / scale) * scaleY);

    return { sx, sy, sw: Math.max(1, sw), sh: Math.max(1, sh) };
}

/**
 * Muat File gambar menjadi HTMLImageElement via Object URL.
 */
function loadImageFromFile(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = (err) => {
            URL.revokeObjectURL(url);
            reject(err);
        };
        img.src = url;
    });
}

/**
 * Crop gambar statis (JPG / PNG / WebP / GIF 1-frame) ke resolusi target.
 */
async function cropStaticImage(file, state, targetType) {
    const img = await loadImageFromFile(file);
    const { sx, sy, sw, sh } = computeCropRect(state, img.naturalWidth, img.naturalHeight);

    const outW = targetType === 'avatar' ? 512 : 1280;
    const outH = targetType === 'avatar' ? 512 : 400;

    const canvas = document.createElement('canvas');
    canvas.width = outW;
    canvas.height = outH;
    const ctx = canvas.getContext('2d');
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sx, sy, sw, sh, 0, 0, outW, outH);

    const isPngOrWebp = file.type === 'image/png' || file.type === 'image/webp';
    const mime = isPngOrWebp ? file.type : 'image/jpeg';
    const ext = mime === 'image/png' ? 'png' : (mime === 'image/webp' ? 'webp' : 'jpg');

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, mime, 0.92));
    if (!blob) {
        throw new Error('Gagal membuat hasil crop gambar.');
    }

    const baseName = (file.name || targetType).replace(/\.[^.]+$/, '');
    return new File([blob], `${baseName}_cropped.${ext}`, { type: mime });
}

/**
 * Crop GIF animasi per-frame sambil mempertahankan seluruh animasi & timing asli.
 */
async function cropAnimatedGif(file, state, targetType) {
    const buffer = await file.arrayBuffer();
    const parsed = parseGIF(buffer);
    const rawFrames = decompressFrames(parsed, true);

    if (!rawFrames || rawFrames.length <= 1) {
        return cropStaticImage(file, state, targetType);
    }

    const gifW = parsed.lsd?.width || state.natW;
    const gifH = parsed.lsd?.height || state.natH;
    const { sx, sy, sw, sh } = computeCropRect(state, gifW, gifH);

    // Batasi frame jika GIF sangat panjang (> 80 frame) agar cepat & hemat ukuran upload
    let frames = rawFrames;
    if (rawFrames.length > 80) {
        const step = Math.ceil(rawFrames.length / 80);
        frames = [];
        for (let i = 0; i < rawFrames.length; i += step) {
            const merged = { ...rawFrames[i] };
            let totalDelay = 0;
            for (let j = i; j < Math.min(rawFrames.length, i + step); j++) {
                totalDelay += rawFrames[j].delay || 80;
            }
            merged.delay = totalDelay;
            frames.push(merged);
        }
    }

    const outW = targetType === 'avatar'
        ? Math.min(360, Math.max(180, Math.round(sw)))
        : Math.min(800, Math.max(400, Math.round(sw)));
    const outH = targetType === 'avatar'
        ? outW
        : Math.max(125, Math.round((outW * 5) / 16));

    const fullCanvas = document.createElement('canvas');
    fullCanvas.width = gifW;
    fullCanvas.height = gifH;
    const fullCtx = fullCanvas.getContext('2d', { willReadFrequently: true });

    const prevCanvas = document.createElement('canvas');
    prevCanvas.width = gifW;
    prevCanvas.height = gifH;
    const prevCtx = prevCanvas.getContext('2d', { willReadFrequently: true });

    const patchCanvas = document.createElement('canvas');
    const patchCtx = patchCanvas.getContext('2d');

    const outCanvas = document.createElement('canvas');
    outCanvas.width = outW;
    outCanvas.height = outH;
    const outCtx = outCanvas.getContext('2d', { willReadFrequently: true });

    const encoder = GIFEncoder();

    for (let i = 0; i < frames.length; i++) {
        const frame = frames[i];
        const { width, height, left, top } = frame.dims;

        if (frame.disposalType === 3) {
            prevCtx.clearRect(0, 0, gifW, gifH);
            prevCtx.drawImage(fullCanvas, 0, 0);
        }

        if (width > 0 && height > 0 && frame.patch) {
            patchCanvas.width = width;
            patchCanvas.height = height;
            const imgData = new ImageData(new Uint8ClampedArray(frame.patch), width, height);
            patchCtx.putImageData(imgData, 0, 0);
            fullCtx.drawImage(patchCanvas, left, top);
        }

        outCtx.clearRect(0, 0, outW, outH);
        outCtx.imageSmoothingEnabled = true;
        outCtx.imageSmoothingQuality = 'medium';
        outCtx.drawImage(fullCanvas, sx, sy, sw, sh, 0, 0, outW, outH);

        const rgba = outCtx.getImageData(0, 0, outW, outH).data;

        // Cek apakah terdapat piksel transparan
        let hasTransparency = false;
        for (let p = 3; p < rgba.length; p += 16) {
            if (rgba[p] < 128) {
                hasTransparency = true;
                break;
            }
        }

        const format = hasTransparency ? 'rgba4444' : 'rgb565';
        const palette = quantize(rgba, 256, {
            format,
            oneBitAlpha: hasTransparency,
            clearAlpha: hasTransparency,
        });
        const indexed = applyPalette(rgba, palette, format);
        const transparentIndex = hasTransparency
            ? palette.findIndex((c) => c.length >= 4 && c[3] === 0)
            : -1;

        encoder.writeFrame(indexed, outW, outH, {
            palette,
            delay: Math.max(20, frame.delay || 80),
            repeat: 0,
            transparent: transparentIndex >= 0,
            transparentIndex: transparentIndex >= 0 ? transparentIndex : 0,
            dispose: hasTransparency ? 2 : 1,
        });

        if (frame.disposalType === 2) {
            fullCtx.clearRect(left, top, width, height);
        } else if (frame.disposalType === 3) {
            fullCtx.clearRect(0, 0, gifW, gifH);
            fullCtx.drawImage(prevCanvas, 0, 0);
        }
    }

    encoder.finish();
    const bytes = encoder.bytes();
    const baseName = (file.name || targetType).replace(/\.[^.]+$/, '');
    return new File([bytes], `${baseName}_cropped.gif`, { type: 'image/gif' });
}

export async function processImageCrop(file, state, targetType) {
    const isGif = file.type === 'image/gif' || /\.gif$/i.test(file.name || '');
    if (isGif) {
        try {
            return await cropAnimatedGif(file, state, targetType);
        } catch (err) {
            console.warn('Animated GIF crop fallback to static:', err);
            return cropStaticImage(file, state, targetType);
        }
    }
    return cropStaticImage(file, state, targetType);
}
