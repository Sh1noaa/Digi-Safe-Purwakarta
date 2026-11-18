import './bootstrap';
import Alpine from 'alpinejs';
import { processImageCrop } from './image-cropper';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    /**
     * Marquee daftar member di Beranda: bergerak terus-menerus, dapat di-drag kanan/kiri,
     * dan tetap mendukung klik/tap ke kartu anggota maupun tombol Follow.
     */
    Alpine.data('memberMarquee', () => ({
        offset: 0,
        halfWidth: 0,
        speed: 44,
        isHovering: false,
        isDragging: false,
        hasDragged: false,
        velocity: 0,
        startX: 0,
        startY: 0,
        lastX: 0,
        lastTime: 0,
        suppressClickUntil: 0,
        lastFrameTime: 0,
        rafId: null,

        init() {
            this.$nextTick(() => {
                this.measure();
                this.lastFrameTime = performance.now();
                const loop = (now) => {
                    this.tick(now);
                    this.rafId = requestAnimationFrame(loop);
                };
                this.rafId = requestAnimationFrame(loop);
            });

            this._onResize = () => this.measure();
            window.addEventListener('resize', this._onResize);

            this._onPointerMove = (e) => this.handlePointerMove(e);
            this._onPointerUp = () => this.handlePointerUp();
            window.addEventListener('pointermove', this._onPointerMove, { passive: true });
            window.addEventListener('pointerup', this._onPointerUp);
            window.addEventListener('pointercancel', this._onPointerUp);
        },

        destroy() {
            if (this.rafId) cancelAnimationFrame(this.rafId);
            window.removeEventListener('resize', this._onResize);
            window.removeEventListener('pointermove', this._onPointerMove);
            window.removeEventListener('pointerup', this._onPointerUp);
            window.removeEventListener('pointercancel', this._onPointerUp);
        },

        measure() {
            const track = this.$refs.track;
            if (!track) return;
            const total = track.scrollWidth;
            if (total > 0) {
                this.halfWidth = total / 2;
                this.offset = this.normalizeOffset(this.offset);
            }
        },

        normalizeOffset(val) {
            if (this.halfWidth <= 0) return 0;
            let x = val % this.halfWidth;
            if (x > 0) x -= this.halfWidth;
            return x;
        },

        applyTransform() {
            const track = this.$refs.track;
            if (track) {
                track.style.transform = `translate3d(${this.offset}px, 0, 0)`;
            }
        },

        tick(now) {
            if (this.halfWidth <= 0) {
                this.measure();
            }
            const dtSec = Math.min(0.05, Math.max(0.001, (now - this.lastFrameTime) / 1000));
            this.lastFrameTime = now;

            if (!this.isDragging) {
                if (Math.abs(this.velocity) > 0.12) {
                    this.offset = this.normalizeOffset(this.offset + this.velocity * (dtSec * 60));
                    this.velocity *= Math.pow(0.93, dtSec * 60);
                } else if (!this.isHovering) {
                    this.velocity = 0;
                    this.offset = this.normalizeOffset(this.offset - this.speed * dtSec);
                }
                this.applyTransform();
            }
        },

        handlePointerDown(e) {
            if (e.button !== undefined && e.button !== 0) return;
            this.measure();
            this.isDragging = true;
            this.hasDragged = false;
            this.velocity = 0;
            this.startX = e.clientX;
            this.startY = e.clientY;
            this.lastX = e.clientX;
            this.lastTime = performance.now();
        },

        handlePointerMove(e) {
            if (!this.isDragging) return;
            const dxTotal = e.clientX - this.startX;
            const dyTotal = e.clientY - this.startY;

            if (!this.hasDragged) {
                if (Math.abs(dyTotal) > 12 && Math.abs(dyTotal) > Math.abs(dxTotal) * 1.15) {
                    this.isDragging = false;
                    return;
                }
                if (Math.abs(dxTotal) > 6) {
                    this.hasDragged = true;
                }
            }

            if (this.hasDragged) {
                const now = performance.now();
                const dx = e.clientX - this.lastX;
                const dt = Math.max(1, now - this.lastTime);
                this.offset = this.normalizeOffset(this.offset + dx);
                this.velocity = Math.max(-35, Math.min(35, (dx / dt) * 16.67));
                this.applyTransform();
            }

            this.lastX = e.clientX;
            this.lastTime = performance.now();
        },

        handlePointerUp() {
            if (!this.isDragging) return;
            this.isDragging = false;
            if (this.hasDragged) {
                this.suppressClickUntil = performance.now() + 140;
                this.hasDragged = false;
            }
        },

        handleClickCapture(e) {
            if (performance.now() < this.suppressClickUntil) {
                e.preventDefault();
                e.stopPropagation();
            }
        },
    }));

    /**
     * Editor Crop Interaktif untuk Foto Profil (1:1) & Banner (16:5), mendukung JPG/PNG/WebP & GIF Animasi.
     */
    Alpine.data('profileImageCropper', () => ({
        cropOpen: false,
        cropTarget: 'avatar', // 'avatar' | 'banner'
        cropFile: null,
        cropSrcUrl: null,
        cropBusy: false,
        cropError: '',
        rawBannerFile: null,
        rawAvatarFile: null,
        natW: 0,
        natH: 0,
        vpW: 260,
        vpH: 260,
        zoom: 1,
        panX: 0,
        panY: 0,
        dragging: false,
        dragStartX: 0,
        dragStartY: 0,
        panStartX: 0,
        panStartY: 0,

        init() {
            this._onMove = (e) => this.onCropPointerMove(e);
            this._onUp = () => this.onCropPointerUp();
            window.addEventListener('pointermove', this._onMove);
            window.addEventListener('pointerup', this._onUp);
            window.addEventListener('pointercancel', this._onUp);
        },

        destroy() {
            window.removeEventListener('pointermove', this._onMove);
            window.removeEventListener('pointerup', this._onUp);
            window.removeEventListener('pointercancel', this._onUp);
            if (this.cropSrcUrl) URL.revokeObjectURL(this.cropSrcUrl);
        },

        get isGifFile() {
            if (!this.cropFile) return false;
            return this.cropFile.type === 'image/gif' || /\.gif$/i.test(this.cropFile.name || '');
        },

        get baseScale() {
            if (!this.natW || !this.natH || !this.vpW || !this.vpH) return 1;
            return Math.max(this.vpW / this.natW, this.vpH / this.natH);
        },

        get imgStyle() {
            if (!this.natW || !this.natH) return 'opacity:0;';
            const w = this.natW * this.baseScale;
            const h = this.natH * this.baseScale;
            return `width:${w}px;height:${h}px;transform:translate(calc(-50% + ${this.panX}px),calc(-50% + ${this.panY}px)) scale(${this.zoom});transform-origin:center center;`;
        },

        openCropFor(file, targetType) {
            if (!file) return;
            if (targetType === 'banner') {
                this.rawBannerFile = file;
            } else {
                this.rawAvatarFile = file;
            }

            if (this.cropSrcUrl) {
                URL.revokeObjectURL(this.cropSrcUrl);
            }

            this.cropTarget = targetType;
            this.cropFile = file;
            this.cropSrcUrl = URL.createObjectURL(file);
            this.cropError = '';
            this.cropBusy = false;
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;
            this.natW = 0;
            this.natH = 0;
            this.cropOpen = true;

            this.$nextTick(() => {
                this.syncViewportDims();
            });
        },

        reopenRawCrop(targetType) {
            const raw = targetType === 'banner' ? this.rawBannerFile : this.rawAvatarFile;
            if (raw) {
                this.openCropFor(raw, targetType);
            }
        },

        syncViewportDims() {
            const vp = this.$refs.cropViewport;
            if (vp) {
                this.vpW = vp.clientWidth || (this.cropTarget === 'avatar' ? 260 : 420);
                this.vpH = vp.clientHeight || (this.cropTarget === 'avatar' ? 260 : 132);
                this.clampPan();
            }
        },

        onImageLoaded(e) {
            this.natW = e.target.naturalWidth || 1;
            this.natH = e.target.naturalHeight || 1;
            this.syncViewportDims();
        },

        clampPan() {
            if (!this.natW || !this.natH) return;
            const scale = this.baseScale * this.zoom;
            const renW = this.natW * scale;
            const renH = this.natH * scale;
            const maxX = Math.max(0, (renW - this.vpW) / 2);
            const maxY = Math.max(0, (renH - this.vpH) / 2);
            this.panX = Math.max(-maxX, Math.min(maxX, this.panX));
            this.panY = Math.max(-maxY, Math.min(maxY, this.panY));
        },

        setZoom(val) {
            this.zoom = Math.max(1, Math.min(3.5, parseFloat(val) || 1));
            this.clampPan();
        },

        onWheelZoom(e) {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.14 : -0.14;
            this.setZoom(this.zoom + delta);
        },

        resetCropView() {
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;
        },

        onCropPointerDown(e) {
            if (e.button !== undefined && e.button !== 0) return;
            e.preventDefault();
            this.dragging = true;
            this.dragStartX = e.clientX;
            this.dragStartY = e.clientY;
            this.panStartX = this.panX;
            this.panStartY = this.panY;
        },

        onCropPointerMove(e) {
            if (!this.dragging) return;
            this.panX = this.panStartX + (e.clientX - this.dragStartX);
            this.panY = this.panStartY + (e.clientY - this.dragStartY);
            this.clampPan();
        },

        onCropPointerUp() {
            this.dragging = false;
        },

        closeCropModal() {
            if (this.cropBusy) return;
            this.cropOpen = false;
        },

        useOriginalImage(onComplete) {
            if (!this.cropFile || this.cropBusy) return;
            const inputEl = this.cropTarget === 'banner'
                ? this.$refs.bannerInput
                : this.$refs.avatarInput;

            if (inputEl) {
                const dt = new DataTransfer();
                dt.items.add(this.cropFile);
                inputEl.files = dt.files;
            }

            const reader = new FileReader();
            reader.onload = (ev) => {
                if (typeof onComplete === 'function') {
                    onComplete(this.cropTarget, ev.target.result, this.cropFile);
                }
                this.cropOpen = false;
            };
            reader.readAsDataURL(this.cropFile);
        },

        async applyCrop(onComplete) {
            if (!this.cropFile || this.cropBusy || !this.natW) return;
            this.syncViewportDims();
            this.cropBusy = true;
            this.cropError = '';

            try {
                const croppedFile = await processImageCrop(
                    this.cropFile,
                    {
                        natW: this.natW,
                        natH: this.natH,
                        vpW: this.vpW,
                        vpH: this.vpH,
                        zoom: this.zoom,
                        panX: this.panX,
                        panY: this.panY,
                    },
                    this.cropTarget
                );

                const inputEl = this.cropTarget === 'banner'
                    ? this.$refs.bannerInput
                    : this.$refs.avatarInput;

                if (inputEl) {
                    const dt = new DataTransfer();
                    dt.items.add(croppedFile);
                    inputEl.files = dt.files;
                }

                const previewReader = new FileReader();
                previewReader.onload = (ev) => {
                    if (typeof onComplete === 'function') {
                        onComplete(this.cropTarget, ev.target.result, croppedFile);
                    }
                    this.cropBusy = false;
                    this.cropOpen = false;
                };
                previewReader.readAsDataURL(croppedFile);
            } catch (err) {
                console.error(err);
                this.cropError = 'Gagal memproses crop gambar. Coba pilih file lain atau gunakan ukuran standar.';
                this.cropBusy = false;
            }
        },
    }));
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const revealElements = document.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-active');
                        obs.unobserve(entry.target);
                    }
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px',
            }
        );

        revealElements.forEach((el) => observer.observe(el));
    } else {
        revealElements.forEach((el) => el.classList.add('reveal-active'));
    }
});
