import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../config.dart';
import '../../../models/dispute_model.dart';
import '../../../services/payments/dispute_repository.dart';

class DisputeDetailScreen extends StatefulWidget {
  const DisputeDetailScreen({super.key});

  @override
  State<DisputeDetailScreen> createState() => _DisputeDetailScreenState();
}

class _DisputeDetailScreenState extends State<DisputeDetailScreen> {
  final DisputeRepository _repository = DisputeRepository();
  Future<DisputeModel>? _future;
  int? _disputeId;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments;
    final parsedId = _resolveDisputeId(args);
    if (parsedId != null && parsedId != _disputeId) {
      _disputeId = parsedId;
      _future = _repository.find(parsedId, preferCache: true);
    }
  }

  int? _resolveDisputeId(Object? args) {
    if (args is Map) {
      final value = args['disputeId'];
      if (value is int) return value;
      if (value is String) return int.tryParse(value);
    }
    return null;
  }

  Future<void> _reload() async {
    if (_disputeId == null) return;
    setState(() {
      _future = _repository.find(_disputeId!, preferCache: false);
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: Text(translations?.dispute ?? 'Dispute detail'),
        actions: [
          IconButton(
            tooltip: translations?.refresh ?? 'Refresh',
            onPressed: _reload,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: _future == null
            ? EmptyLayout(
                title: translations?.noDataFound ?? 'Missing dispute reference',
                subtitle: translations?.noDataFoundDesc ??
                    'We could not determine which dispute to display.',
                buttonText: translations?.goBack ?? 'Go back',
                onTap: () => route.pop(context),
              )
            : FutureBuilder<DisputeModel>(
                future: _future,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator());
                  }
                  if (snapshot.hasError) {
                    return EmptyLayout(
                      title: translations?.error ?? 'Something went wrong',
                      subtitle: snapshot.error.toString(),
                      buttonText: translations?.retry ?? 'Retry',
                      onTap: _reload,
                    );
                  }
                  final dispute = snapshot.data;
                  if (dispute == null) {
                    return EmptyLayout(
                      title: translations?.noDataFound ?? 'Dispute not found',
                      subtitle: translations?.noDataFoundDesc ??
                          'The requested dispute is no longer available.',
                      buttonText: translations?.goBack ?? 'Go back',
                      onTap: () => route.pop(context),
                    );
                  }
                  return RefreshIndicator(
                    onRefresh: _reload,
                    child: ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        _buildSummaryCard(theme, dispute),
                        const SizedBox(height: 16),
                        _buildTimeline(theme, dispute),
                        const SizedBox(height: 16),
                        _buildMessages(theme, dispute),
                      ],
                    ),
                  );
                },
              ),
      ),
    );
  }

  Widget _buildSummaryCard(ThemeData theme, DisputeModel dispute) {
    final dateFormatter = DateFormat.yMMMEd();
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Dispute #${dispute.id}',
              style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                Chip(
                  label: Text(dispute.stage.toUpperCase()),
                  backgroundColor: theme.colorScheme.primaryContainer,
                  labelStyle: theme.textTheme.labelLarge?.copyWith(
                    color: theme.colorScheme.onPrimaryContainer,
                  ),
                ),
                Chip(
                  label: Text(dispute.status.toUpperCase()),
                  backgroundColor: theme.colorScheme.secondaryContainer,
                  labelStyle: theme.textTheme.labelLarge?.copyWith(
                    color: theme.colorScheme.onSecondaryContainer,
                  ),
                ),
                if (dispute.deadlineAt != null)
                  Chip(
                    avatar: const Icon(Icons.timer_outlined, size: 18),
                    label: Text('Deadline ${dateFormatter.format(dispute.deadlineAt!)}'),
                  ),
                Chip(
                  avatar: const Icon(Icons.handshake_outlined, size: 18),
                  label: Text('Job #${dispute.jobId}'),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (dispute.summary != null)
              Text(
                dispute.summary!,
                style: theme.textTheme.bodyMedium,
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildTimeline(ThemeData theme, DisputeModel dispute) {
    if (dispute.events.isEmpty) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text(
            translations?.noDataFound ?? 'No events recorded yet.',
            style: theme.textTheme.bodyMedium,
          ),
        ),
      );
    }
    final dateFormatter = DateFormat.yMMMd().add_jm();
    return Card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              translations?.activity ?? 'Timeline',
              style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
            ),
          ),
          const Divider(height: 1),
          for (final event in dispute.events)
            ListTile(
              leading: const Icon(Icons.bolt_outlined),
              title: Text(event.action.replaceAll('_', ' ')),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(dateFormatter.format(event.createdAt)),
                  if (event.note != null && event.note!.isNotEmpty)
                    Text(event.note!),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildMessages(ThemeData theme, DisputeModel dispute) {
    if (dispute.messages.isEmpty) {
      return const SizedBox.shrink();
    }
    final dateFormatter = DateFormat.MMMd().add_jm();
    return Card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Text(
              translations?.messages ?? 'Messages',
              style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
            ),
          ),
          const Divider(height: 1),
          for (final message in dispute.messages)
            ListTile(
              leading: const Icon(Icons.chat_bubble_outline),
              title: Text(message.body),
              subtitle: Text('${message.visibility} • ${dateFormatter.format(message.createdAt)}'),
            ),
        ],
      ),
    );
  }
}
