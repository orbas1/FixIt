import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../config.dart';
import '../../../models/dispute_model.dart';
import '../../../services/payments/dispute_repository.dart';

class DisputeCenterScreen extends StatefulWidget {
  const DisputeCenterScreen({super.key});

  @override
  State<DisputeCenterScreen> createState() => _DisputeCenterScreenState();
}

class _DisputeCenterScreenState extends State<DisputeCenterScreen> {
  final DisputeRepository _repository = DisputeRepository();
  final TextEditingController _searchCtrl = TextEditingController();
  final FocusNode _searchFocus = FocusNode();

  late Future<List<DisputeModel>> _future;
  List<DisputeModel> _disputes = const [];
  String _query = '';
  final Set<String> _stageFilters = {};
  final Set<String> _statusFilters = {};
  bool _showResolved = false;

  @override
  void initState() {
    super.initState();
    _future = _loadDisputes();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _searchFocus.dispose();
    super.dispose();
  }

  Future<List<DisputeModel>> _loadDisputes({bool preferCache = true}) async {
    final disputes = await _repository.all(preferCache: preferCache);
    if (!mounted) return disputes;
    setState(() {
      _disputes = disputes;
    });
    return disputes;
  }

  Future<void> _refresh() async {
    setState(() {
      _future = _loadDisputes(preferCache: false);
    });
    await _future;
  }

  Iterable<DisputeModel> get _filteredDisputes {
    Iterable<DisputeModel> workingSet = _disputes;

    if (!_showResolved) {
      workingSet = workingSet.where((dispute) => dispute.isOpen);
    }

    if (_stageFilters.isNotEmpty) {
      workingSet = workingSet.where((dispute) => _stageFilters.contains(dispute.stage));
    }

    if (_statusFilters.isNotEmpty) {
      workingSet = workingSet.where((dispute) => _statusFilters.contains(dispute.status));
    }

    if (_query.isNotEmpty) {
      final lower = _query.toLowerCase();
      workingSet = workingSet.where((dispute) {
        final idMatch = dispute.id.toString().contains(lower);
        final jobMatch = dispute.jobId.toString().contains(lower);
        final stageMatch = dispute.stage.toLowerCase().contains(lower);
        final statusMatch = dispute.status.toLowerCase().contains(lower);
        final summaryMatch =
            dispute.summary?.toLowerCase().contains(lower) ?? false;
        return idMatch || jobMatch || stageMatch || statusMatch || summaryMatch;
      });
    }

    return workingSet;
  }

  void _toggleFilter(Set<String> target, String value) {
    setState(() {
      if (target.contains(value)) {
        target.remove(value);
      } else {
        target.add(value);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dispute center'),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            onPressed: _refresh,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      floatingActionButton: _disputes.isEmpty
          ? null
          : FloatingActionButton.extended(
              onPressed: () {
                setState(() {
                  _stageFilters.clear();
                  _statusFilters.clear();
                  _searchCtrl.clear();
                  _query = '';
                  _showResolved = false;
                });
              },
              icon: const Icon(Icons.clear_all),
              label: const Text('Reset filters'),
            ),
      body: SafeArea(
        child: FutureBuilder<List<DisputeModel>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting &&
                _disputes.isEmpty) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError && _disputes.isEmpty) {
              return EmptyLayout(
                title: 'Unable to load disputes',
                subtitle: snapshot.error.toString(),
                buttonText: translations?.retry ?? 'Retry',
                onTap: _refresh,
              );
            }
            if (_disputes.isEmpty) {
              return EmptyLayout(
                title: 'No disputes filed',
                subtitle:
                    'When a dispute is opened you will see the timeline, deadlines, and outcome here.',
                buttonText: translations?.refresh ?? 'Refresh',
                onTap: _refresh,
              );
            }

            final disputes = _filteredDisputes.toList(growable: false);
            return RefreshIndicator(
              onRefresh: _refresh,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildSearch(theme),
                  const SizedBox(height: 12),
                  _buildFilterPanel(theme),
                  const SizedBox(height: 16),
                  if (disputes.isEmpty)
                    EmptyLayout(
                      title: 'No disputes match the filters',
                      subtitle:
                          'Adjust the filters to widen the scope or clear the search query.',
                      buttonText: 'Reset filters',
                      onTap: () {
                        setState(() {
                          _stageFilters.clear();
                          _statusFilters.clear();
                          _searchCtrl.clear();
                          _query = '';
                          _showResolved = false;
                        });
                      },
                    )
                  else
                    ...disputes.map(_buildDisputeCard),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildSearch(ThemeData theme) {
    return TextField(
      controller: _searchCtrl,
      focusNode: _searchFocus,
      decoration: InputDecoration(
        prefixIcon: const Icon(Icons.search),
        labelText: 'Search disputes',
        suffixIcon: _query.isEmpty
            ? null
            : IconButton(
                tooltip: 'Clear',
                icon: const Icon(Icons.clear),
                onPressed: () {
                  _searchCtrl.clear();
                  setState(() {
                    _query = '';
                  });
                },
              ),
      ),
      onChanged: (value) => setState(() {
        _query = value.trim();
      }),
    );
  }

  Widget _buildFilterPanel(ThemeData theme) {
    const stageOrder = <String, String>{
      'intake': 'Intake',
      'mediation': 'Mediation',
      'arbitration': 'Arbitration',
      'resolution': 'Resolution',
    };
    const statusOrder = <String, String>{
      'open': 'Open',
      'pending': 'Pending',
      'action_required': 'Action required',
      'resolved': 'Resolved',
      'cancelled': 'Cancelled',
    };

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final entry in stageOrder.entries)
              FilterChip(
                label: Text(entry.value),
                selected: _stageFilters.contains(entry.key),
                onSelected: (_) => _toggleFilter(_stageFilters, entry.key),
              ),
          ],
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final entry in statusOrder.entries)
              FilterChip(
                label: Text(entry.value),
                selected: _statusFilters.contains(entry.key),
                onSelected: (_) => _toggleFilter(_statusFilters, entry.key),
              ),
            FilterChip(
              label: const Text('Include resolved'),
              selected: _showResolved,
              onSelected: (value) => setState(() {
                _showResolved = value;
              }),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildDisputeCard(DisputeModel dispute) {
    final theme = Theme.of(context);
    final dateFormatter = DateFormat.yMMMd().add_jm();
    final stageLabel = dispute.stage.replaceAll('_', ' ');
    final statusLabel = dispute.status.replaceAll('_', ' ');
    final deadline = dispute.deadlineAt != null
        ? 'Due ${dateFormatter.format(dispute.deadlineAt!)}'
        : 'No deadline scheduled';

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () {
          route.pushNamed(
            context,
            routeName.disputeDetails,
            arg: {'disputeId': dispute.id, 'dispute': dispute},
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Dispute #${dispute.id}',
                          style: theme.textTheme.titleMedium
                              ?.copyWith(fontWeight: FontWeight.w700),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Job #${dispute.jobId}',
                          style: theme.textTheme.bodySmall
                              ?.copyWith(color: theme.colorScheme.outline),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Chip(
                        avatar: const Icon(Icons.flag_outlined, size: 18),
                        label: Text(stageLabel.toUpperCase()),
                      ),
                      const SizedBox(height: 6),
                      Chip(
                        avatar: const Icon(Icons.shield_outlined, size: 18),
                        backgroundColor: dispute.isResolved
                            ? theme.colorScheme.secondaryContainer
                            : theme.colorScheme.surfaceVariant,
                        label: Text(statusLabel.toUpperCase()),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                dispute.summary ?? 'No summary provided by parties yet.',
                style: theme.textTheme.bodyMedium,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(
                    Icons.timer_outlined,
                    color: theme.colorScheme.primary,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      deadline,
                      style: theme.textTheme.bodyMedium,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              if (dispute.events.isNotEmpty)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Recent activity',
                      style: theme.textTheme.labelLarge,
                    ),
                    const SizedBox(height: 6),
                    ...dispute.events
                        .take(3)
                        .map((event) => ListTile(
                              contentPadding: EdgeInsets.zero,
                              dense: true,
                              leading: const Icon(Icons.bolt_outlined),
                              title: Text(event.action.replaceAll('_', ' ')),
                              subtitle: Text(dateFormatter.format(event.createdAt)),
                            )),
                  ],
                ),
            ],
          ),
        ),
      ),
    );
  }
}
