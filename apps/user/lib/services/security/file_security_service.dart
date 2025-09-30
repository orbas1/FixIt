import 'dart:async';
import 'package:crypto/crypto.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mime/mime.dart';
import 'package:path/path.dart' as p;

class FileSecurityException implements Exception {
  FileSecurityException(this.message);

  final String message;

  @override
  String toString() => message;
}

class FileSecurityEvaluation {
  FileSecurityEvaluation({
    required this.fileName,
    required this.fileSize,
    required this.mimeType,
    required this.sha256,
  });

  final String fileName;
  final int fileSize;
  final String? mimeType;
  final String sha256;
}

enum FileUseCase { image, document }

class FileSecurityService {
  const FileSecurityService();

  static const _maxFileSizeBytes = 25 * 1024 * 1024;
  static const _imageExtensions = {'jpg', 'jpeg', 'png', 'gif', 'heic', 'webp'};
  static const _documentExtensions = {'pdf', 'doc', 'docx', 'xls', 'xlsx', 'mp4', 'mov', 'm4v'};
  static const _disallowedExtensions = {'exe', 'bat', 'cmd', 'sh', 'apk', 'ipa'};

  Future<FileSecurityEvaluation> evaluateXFile(XFile file, {FileUseCase useCase = FileUseCase.image}) async {
    final size = await file.length();
    if (size > _maxFileSizeBytes) {
      throw FileSecurityException('File exceeds the 25MB upload limit.');
    }

    final extension = p.extension(file.path).replaceAll('.', '').toLowerCase();
    if (_disallowedExtensions.contains(extension)) {
      throw FileSecurityException('Files of type .$extension are not permitted.');
    }

    final allowedExtensions = useCase == FileUseCase.image ? _imageExtensions : {..._imageExtensions, ..._documentExtensions};
    if (allowedExtensions.isNotEmpty && extension.isNotEmpty && !allowedExtensions.contains(extension)) {
      throw FileSecurityException('Unsupported file type .$extension for this action.');
    }

    final headerBytes = await _readHeaderBytes(file);
    final mimeType = lookupMimeType(file.path, headerBytes: headerBytes);
    if (mimeType != null && useCase == FileUseCase.image && !mimeType.startsWith('image/')) {
      throw FileSecurityException('Please upload image formats only.');
    }

    if (mimeType != null &&
        useCase == FileUseCase.document &&
        !(mimeType.startsWith('application/') || mimeType.startsWith('image/') || mimeType.startsWith('video/'))) {
      throw FileSecurityException('Unsupported document MIME type: $mimeType');
    }

    final sha256Digest = sha256.convert(await file.readAsBytes()).toString();

    return FileSecurityEvaluation(
      fileName: p.basename(file.path),
      fileSize: size,
      mimeType: mimeType,
      sha256: sha256Digest,
    );
  }

  Future<List<int>> _readHeaderBytes(XFile file) async {
    final stream = file.openRead(0, 64);
    final completer = Completer<List<int>>();
    stream.listen(
      (event) {
        if (!completer.isCompleted) {
          completer.complete(event);
        }
      },
      onError: (Object error, StackTrace stackTrace) {
        if (!completer.isCompleted) {
          completer.completeError(error, stackTrace);
        }
      },
      onDone: () {
        if (!completer.isCompleted) {
          completer.complete(<int>[]);
        }
      },
      cancelOnError: true,
    );
    return completer.future;
  }
}
