import 'dart:async';

import 'package:fixit_user/models/feed_job_model.dart';
import 'package:fixit_user/services/error/exceptions.dart';
import 'package:fixit_user/services/feed/feed_job_repository.dart';
import 'package:flutter/foundation.dart';

class FeedJobDetailProvider extends ChangeNotifier {
  FeedJobDetailProvider({FeedJobRepository? repository})
      : _repository = repository ?? FeedJobRepository();

  final FeedJobRepository _repository;

  FeedJobModel? _job;
  bool _loading = false;
  bool _refreshing = false;
  bool _isBookmarked = false;
  bool _isSubmitting = false;
  String? _error;

  FeedJobModel? get job => _job;
  bool get isLoading => _loading;
  bool get isRefreshing => _refreshing;
  bool get isSubmitting => _isSubmitting;
  bool get isBookmarked => _isBookmarked;
  String? get errorMessage => _error;
  bool get hasError => _error != null;
  bool get canSubmitProposal => (_job?.status ?? '').toLowerCase() == 'open';

  Future<void> bootstrap({required int jobId, FeedJobModel? initialJob}) async {
    if (_job != null && _job!.id == jobId) return;

    _job = initialJob;
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _job = await _repository.fetchJobDetail(jobId);
      _isBookmarked = _job?.bidsSummary?['bookmarked'] == true;
    } catch (error, stackTrace) {
      debugPrint('FeedJobDetailProvider bootstrap failed: $error');
      debugPrintStack(stackTrace: stackTrace);
      _error = error.toString();
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> refresh() async {
    final current = _job;
    if (current == null) return;
    _refreshing = true;
    _error = null;
    notifyListeners();
    try {
      _job = await _repository.refreshJobDetail(current.id);
      _isBookmarked = _job?.bidsSummary?['bookmarked'] == true;
    } catch (error, stackTrace) {
      debugPrint('FeedJobDetailProvider refresh failed: $error');
      debugPrintStack(stackTrace: stackTrace);
      _error = error.toString();
    } finally {
      _refreshing = false;
      notifyListeners();
    }
  }

  Future<void> submitBid({
    required double amount,
    String? message,
    int? durationDays,
  }) async {
    final current = _job;
    if (current == null) return;
    _isSubmitting = true;
    _error = null;
    notifyListeners();
    try {
      await _repository.submitBid(
        jobId: current.id,
        amount: amount,
        message: message,
        durationDays: durationDays,
      );
      await refresh();
    } on RemoteException catch (error) {
      _error = error.message;
      rethrow;
    } catch (error, stackTrace) {
      debugPrint('FeedJobDetailProvider submitBid failed: $error');
      debugPrintStack(stackTrace: stackTrace);
      _error = error.toString();
      rethrow;
    } finally {
      _isSubmitting = false;
      notifyListeners();
    }
  }

  Future<void> toggleBookmark() async {
    final current = _job;
    if (current == null) return;
    final desired = !_isBookmarked;
    _isBookmarked = desired;
    notifyListeners();
    try {
      await _repository.toggleBookmark(jobId: current.id, shouldBookmark: desired);
    } catch (error, stackTrace) {
      debugPrint('FeedJobDetailProvider toggleBookmark failed: $error');
      debugPrintStack(stackTrace: stackTrace);
      _isBookmarked = !desired;
      _error = error.toString();
      notifyListeners();
    }
  }
}
