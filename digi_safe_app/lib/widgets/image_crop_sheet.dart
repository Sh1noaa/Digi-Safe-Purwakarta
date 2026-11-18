import 'dart:io';
import 'dart:math' as math;

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image/image.dart' as img;

import '../core/theme.dart';

/// Pure-Flutter Interaktif Image Cropper untuk Avatar (1:1) & Banner (16:5)
/// plus opsi mempertahankan animasi .gif asli tanpa merusaknya.
class ImageCropSheet extends StatefulWidget {
  final File sourceFile;
  final bool isAvatar;
  final bool isDark;
  final bool isEn;

  const ImageCropSheet({
    super.key,
    required this.sourceFile,
    required this.isAvatar,
    required this.isDark,
    required this.isEn,
  });

  static Future<File?> open(
    BuildContext context, {
    required File sourceFile,
    required bool isAvatar,
    required bool isDark,
    required bool isEn,
  }) {
    return showModalBottomSheet<File>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => ImageCropSheet(
        sourceFile: sourceFile,
        isAvatar: isAvatar,
        isDark: isDark,
        isEn: isEn,
      ),
    );
  }

  @override
  State<ImageCropSheet> createState() => _ImageCropSheetState();
}

class _ImageCropSheetState extends State<ImageCropSheet> {
  final TransformationController _txCtrl = TransformationController();
  int _imgW = 0;
  int _imgH = 0;
  double _baseScale = 1.0;
  double _zoom = 1.0;
  bool _loadingMeta = true;
  bool _cropping = false;
  bool _isGif = false;
  double _cropBoxW = 240;
  double _cropBoxH = 240;

  @override
  void initState() {
    super.initState();
    _isGif = widget.sourceFile.path.toLowerCase().endsWith('.gif');
    _inspectImage();
  }

  @override
  void dispose() {
    _txCtrl.dispose();
    super.dispose();
  }

  Future<void> _inspectImage() async {
    try {
      final bytes = await widget.sourceFile.readAsBytes();
      final decoded = await compute(_decodeDimensions, bytes);
      if (mounted && decoded != null) {
        setState(() {
          _imgW = decoded.$1;
          _imgH = decoded.$2;
          _loadingMeta = false;
        });
      } else if (mounted) {
        setState(() => _loadingMeta = false);
      }
    } catch (_) {
      if (mounted) setState(() => _loadingMeta = false);
    }
  }

  static (int, int)? _decodeDimensions(Uint8List bytes) {
    final d = img.decodeImage(bytes);
    if (d == null) return null;
    return (d.width, d.height);
  }

  Future<void> _applyCrop() async {
    if (_imgW <= 0 || _imgH <= 0) {
      Navigator.of(context).pop(widget.sourceFile);
      return;
    }

    setState(() => _cropping = true);
    try {
      final bytes = await widget.sourceFile.readAsBytes();
      final mat = _txCtrl.value;
      final scale = mat.getMaxScaleOnAxis().clamp(0.1, 20.0);
      final tx = mat.getTranslation().x;
      final ty = mat.getTranslation().y;

      // Hitung koordinat persegi crop terhadap gambar tampilan InteractiveViewer
      final visX = (-tx / scale).clamp(0.0, _imgW * _baseScale);
      final visY = (-ty / scale).clamp(0.0, _imgH * _baseScale);
      final visW = _cropBoxW / scale;
      final visH = _cropBoxH / scale;

      final factorX = _imgW / (_imgW * _baseScale);
      final factorY = _imgH / (_imgH * _baseScale);

      final cropX = (visX * factorX).round().clamp(0, math.max(0, _imgW - 2));
      final cropY = (visY * factorY).round().clamp(0, math.max(0, _imgH - 2));
      final cropW = (visW * factorX).round().clamp(16, _imgW - cropX);
      final cropH = (visH * factorY).round().clamp(16, _imgH - cropY);

      final outW = widget.isAvatar ? 512 : 1280;
      final outH = widget.isAvatar ? 512 : 400;

      final croppedBytes = await compute(_cropEncodeWorker, {
        'bytes': bytes,
        'x': cropX,
        'y': cropY,
        'w': cropW,
        'h': cropH,
        'outW': outW,
        'outH': outH,
      });

      final tempFile = File('${Directory.systemTemp.path}/digisafe_crop_${DateTime.now().millisecondsSinceEpoch}.jpg');
      await tempFile.writeAsBytes(croppedBytes);

      if (mounted) Navigator.of(context).pop(tempFile);
    } catch (_) {
      if (mounted) Navigator.of(context).pop(widget.sourceFile);
    } finally {
      if (mounted) setState(() => _cropping = false);
    }
  }

  static Uint8List _cropEncodeWorker(Map<String, dynamic> params) {
    final decoded = img.decodeImage(params['bytes'] as Uint8List)!;
    final cropped = img.copyCrop(
      decoded,
      x: params['x'] as int,
      y: params['y'] as int,
      width: params['w'] as int,
      height: params['h'] as int,
    );
    final resized = img.copyResize(
      cropped,
      width: params['outW'] as int,
      height: params['outH'] as int,
      interpolation: img.Interpolation.cubic,
    );
    return Uint8List.fromList(img.encodeJpg(resized, quality: 90));
  }

  @override
  Widget build(BuildContext context) {
    final isDark = widget.isDark;
    final isEn = widget.isEn;
    final maxW = MediaQuery.of(context).size.width - 48;
    _cropBoxW = widget.isAvatar ? math.min(250, maxW) : math.min(340, maxW);
    _cropBoxH = widget.isAvatar ? _cropBoxW : (_cropBoxW * 5 / 16);

    if (!_loadingMeta && _imgW > 0 && _imgH > 0) {
      final scaleX = _cropBoxW / _imgW;
      final scaleY = _cropBoxH / _imgH;
      _baseScale = math.max(scaleX, scaleY);
    }

    return Container(
      padding: const EdgeInsets.fromLTRB(20, 18, 20, 26),
      decoration: BoxDecoration(
        color: AppColors.cardBg(isDark),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(color: AppColors.sky.withValues(alpha: 0.12), shape: BoxShape.circle),
                child: Icon(widget.isAvatar ? Icons.crop_square_rounded : Icons.panorama_rounded, color: AppColors.sky, size: 18),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.isAvatar
                          ? (isEn ? 'Crop Profile Photo (1:1)' : 'Potong Foto Profil (1:1)')
                          : (isEn ? 'Crop Profile Banner (16:5)' : 'Potong Banner Profil (16:5)'),
                      style: AppTheme.heading(isDark, size: 15.5),
                    ),
                    Text(
                      isEn ? 'Drag or pinch to frame perfectly' : 'Geser atau cubit layar untuk menyesuaikan posisi',
                      style: AppTheme.body(isDark, size: 11, color: AppColors.textSecondary(isDark)),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.of(context).pop(null),
                icon: const Icon(Icons.close_rounded),
              ),
            ],
          ),
          const SizedBox(height: 16),

          if (_loadingMeta)
            const Padding(
              padding: EdgeInsets.all(40),
              child: CircularProgressIndicator(),
            )
          else
            Container(
              width: _cropBoxW,
              height: _cropBoxH,
              decoration: BoxDecoration(
                color: Colors.black,
                borderRadius: BorderRadius.circular(widget.isAvatar ? _cropBoxW / 2 : 18),
                border: Border.all(color: AppColors.sky, width: 2.2),
              ),
              clipBehavior: Clip.antiAlias,
              child: InteractiveViewer(
                transformationController: _txCtrl,
                constrained: false,
                minScale: 1.0,
                maxScale: 4.5,
                onInteractionUpdate: (_) {
                  setState(() => _zoom = _txCtrl.value.getMaxScaleOnAxis().clamp(1.0, 4.5));
                },
                child: SizedBox(
                  width: _imgW > 0 ? _imgW * _baseScale : _cropBoxW,
                  height: _imgH > 0 ? _imgH * _baseScale : _cropBoxH,
                  child: Image.file(
                    widget.sourceFile,
                    fit: BoxFit.fill,
                  ),
                ),
              ),
            ),

          const SizedBox(height: 14),
          Row(
            children: [
              const Icon(Icons.zoom_out_rounded, size: 18, color: AppColors.muted),
              Expanded(
                child: Slider(
                  value: _zoom.clamp(1.0, 4.0),
                  min: 1.0,
                  max: 4.0,
                  activeColor: AppColors.sky,
                  onChanged: (v) {
                    setState(() {
                      _zoom = v;
                      final prev = _txCtrl.value;
                      final tx = prev.getTranslation().x;
                      final ty = prev.getTranslation().y;
                      _txCtrl.value = Matrix4.identity()
                        ..translateByDouble(tx, ty, 0.0, 1.0)
                        ..scaleByDouble(v, v, 1.0, 1.0);
                    });
                  },
                ),
              ),
              const Icon(Icons.zoom_in_rounded, size: 18, color: AppColors.sky),
            ],
          ),

          if (_isGif) ...[
            const SizedBox(height: 6),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _cropping ? null : () => Navigator.of(context).pop(widget.sourceFile),
                icon: const Icon(Icons.gif_box_rounded, color: AppColors.sky, size: 18),
                label: Text(
                  isEn ? 'Use Original Animated GIF' : 'Gunakan GIF Animasi Utuh (Tanpa Mati)',
                  style: AppTheme.heading(isDark, size: 12, color: AppColors.sky),
                ),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  side: BorderSide(color: AppColors.sky.withValues(alpha: 0.45)),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                ),
              ),
            ),
            const SizedBox(height: 8),
          ],

          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _cropping ? null : _applyCrop,
              icon: _cropping
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.lime))
                  : const Icon(Icons.check_rounded, size: 18, color: AppColors.lime),
              label: Text(
                isEn ? 'Apply Crop & Save' : 'Terapkan Potongan Gambar',
                style: AppTheme.heading(false, size: 13, color: Colors.white),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.ink,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
