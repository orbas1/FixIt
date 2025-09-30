import 'dart:ui' show FontFeature;

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../config.dart';
import '../../../models/escrow_model.dart';
import '../../../services/payments/escrow_repository.dart';
import '../../../services/payments/stripe_payment_sheet_service.dart';

enum _EscrowAction { release, refund }

class EscrowCenterScreen extends StatefulWidget {
  const EscrowCenterScreen({super.key});

  @override
  State<EscrowCenterScreen> createState() => _EscrowCenterScreenState();
}

class _EscrowCenterScreenState extends State<EscrowCenterScreen> {
  final EscrowRepository _repository = EscrowRepository();
  final StripePaymentSheetService _stripe = StripePaymentSheetService();
  final TextEditingController _searchCtrl = TextEditingController();
  final FocusNode _searchFocus = FocusNode();
  final NumberFormat _currencyFormat = NumberFormat.simpleCurrency();

  late Future<List<EscrowModel>> _future;
  List<EscrowModel> _escrows = const [];
  String _query = '';
  String _statusFilter = 'all';
  bool _includeClosed = true;
  bool _isFunding = false;

  @override
  void initState() {
    super.initState();
    _future = _loadEscrows();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _searchFocus.dispose();
    super.dispose();
  }

  Future<List<EscrowModel>> _loadEscrows({bool preferCache = true}) async {
    final escrows = await _repository.all(preferCache: preferCache);
    if (!mounted) return escrows;
    setState(() {
      _escrows = escrows;
    });
    return escrows;
  }

  Future<void> _refresh() async {
    setState(() {
      _future = _loadEscrows(preferCache: false);
    });
    await _future;
  }

  Future<void> _fundEscrow(EscrowModel escrow) async {
    if (_isFunding) return;
    setState(() => _isFunding = true);
    try {
      final intent = await _repository.createPaymentIntent(escrow.id);
      await _stripe.present(intent);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Payment confirmed. Escrow funded.')),
      );
      await _refresh();
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Unable to fund escrow: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _isFunding = false);
      }
    }
  }

  void _onQueryChanged(String value) {
    setState(() {
      _query = value.trim();
    });
  }

  Iterable<EscrowModel> get _filteredEscrows {
    Iterable<EscrowModel> workingSet = _escrows;

    if (!_includeClosed) {
      workingSet = workingSet.where((escrow) => !escrow.isClosed);
    }

    if (_statusFilter != 'all') {
      workingSet = workingSet.where((escrow) => escrow.status == _statusFilter);
    }

    if (_query.isNotEmpty) {
      final normalized = _query.toLowerCase();
      workingSet = workingSet.where((escrow) {
        final reference = escrow.holdReference?.toLowerCase() ?? '';
        final idMatch = escrow.id.toString().contains(normalized);
        final statusMatch = escrow.status.toLowerCase().contains(normalized);
        final currencyMatch = escrow.currency.toLowerCase().contains(normalized);
        final ledgerMatch = escrow.ledger.any((entry) =>
            entry.reference?.toLowerCase().contains(normalized) ?? false);
        return reference.contains(normalized) ||
            idMatch ||
            statusMatch ||
            currencyMatch ||
            ledgerMatch;
      });
    }

    return workingSet;
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'funded':
        return 'Funded';
      case 'released':
        return 'Released';
      case 'refunded':
        return 'Refunded';
      case 'cancelled':
        return 'Cancelled';
      case 'pending':
        return 'Pending';
      default:
        return status.toUpperCase();
    }
  }

  Color _statusColor(ThemeData theme, EscrowModel escrow) {
    switch (escrow.status) {
      case 'funded':
        return theme.colorScheme.primaryContainer;
      case 'released':
        return theme.colorScheme.secondaryContainer;
      case 'refunded':
        return theme.colorScheme.tertiaryContainer ?? theme.colorScheme.surfaceVariant;
      case 'cancelled':
        return theme.colorScheme.errorContainer;
      default:
        return theme.colorScheme.surfaceVariant;
    }
  }

  String _formatCurrency(EscrowModel escrow, double amount) {
    try {
      return NumberFormat.simpleCurrency(name: escrow.currency).format(amount);
    } catch (_) {
      return _currencyFormat.format(amount);
    }
  }

  Future<void> _showActionSheet(EscrowModel escrow, _EscrowAction action) async {
    final controller = TextEditingController(text: escrow.availableAmount.toStringAsFixed(2));
    final formKey = GlobalKey<FormState>();
    final maxAmount = action == _EscrowAction.release
        ? escrow.availableAmount
        : escrow.availableAmount;

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        final padding = MediaQuery.of(context).viewInsets;
        return Padding(
          padding: EdgeInsets.only(bottom: padding.bottom),
          child: Form(
            key: formKey,
            child: ListView(
              shrinkWrap: true,
              padding: const EdgeInsets.all(24),
              children: [
                Text(
                  action == _EscrowAction.release
                      ? 'Release funds'
                      : 'Refund buyer',
                  style: Theme.of(context)
                      .textTheme
                      .titleMedium
                      ?.copyWith(fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 8),
                Text(
                  'Escrow #${escrow.id} • ${_statusLabel(escrow.status)}',
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                const SizedBox(height: 24),
                TextFormField(
                  controller: controller,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: 'Amount (${escrow.currency})',
                    helperText: 'Available ${_formatCurrency(escrow, maxAmount)}',
                  ),
                  validator: (value) {
                    final raw = value?.trim();
                    if (raw == null || raw.isEmpty) {
                      return 'Enter an amount';
                    }
                    final parsed = double.tryParse(raw);
                    if (parsed == null) {
                      return 'Enter a valid number';
                    }
                    if (parsed <= 0) {
                      return 'Amount must be greater than zero';
                    }
                    if (parsed > maxAmount + 0.0001) {
                      return 'Amount cannot exceed ${_formatCurrency(escrow, maxAmount)}';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 24),
                FilledButton(
                  onPressed: () async {
                    if (!formKey.currentState!.validate()) {
                      return;
                    }
                    final amount = double.parse(controller.text.trim());
                    await _performAction(escrow, action, amount);
                    if (mounted) {
                      Navigator.of(context).pop();
                    }
                  },
                  child: Text(action == _EscrowAction.release ? 'Release' : 'Refund'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _performAction(
      EscrowModel escrow, _EscrowAction action, double amount) async {
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    try {
      late EscrowModel updated;
      if (action == _EscrowAction.release) {
        updated = await _repository.release(escrow.id, amount);
      } else {
        updated = await _repository.refund(escrow.id, amount);
      }
      if (!mounted) return;
      setState(() {
        _escrows = _escrows
            .map((item) => item.id == updated.id ? updated : item)
            .toList(growable: false);
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            action == _EscrowAction.release
                ? 'Released ${_formatCurrency(updated, amount)}'
                : 'Refunded ${_formatCurrency(updated, amount)}',
          ),
        ),
      );
    } catch (error) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Operation failed: $error')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Escrow & payments'),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            onPressed: _refresh,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: SafeArea(
        child: FutureBuilder<List<EscrowModel>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting &&
                _escrows.isEmpty) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError && _escrows.isEmpty) {
              return EmptyLayout(
                title: 'Unable to load escrows',
                subtitle: snapshot.error.toString(),
                buttonText: translations?.retry ?? 'Retry',
                onTap: _refresh,
              );
            }
            if (_escrows.isEmpty) {
              return EmptyLayout(
                title: 'No escrows yet',
                subtitle: 'Fund a booking to see escrow activity and ledger events.',
                buttonText: translations?.refresh ?? 'Refresh',
                onTap: _refresh,
              );
            }

            final escrows = _filteredEscrows.toList(growable: false);
            return RefreshIndicator(
              onRefresh: _refresh,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildSearch(theme),
                  const SizedBox(height: 12),
                  _buildFilters(theme),
                  const SizedBox(height: 16),
                  if (escrows.isEmpty)
                    EmptyLayout(
                      title: 'No escrows match the filters',
                      subtitle:
                          'Try widening your filters or clearing the search criteria.',
                      buttonText: 'Reset filters',
                      onTap: () {
                        setState(() {
                          _query = '';
                          _searchCtrl.clear();
                          _statusFilter = 'all';
                          _includeClosed = true;
                        });
                      },
                    )
                  else
                    ...escrows.map(_buildEscrowCard),
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
      textInputAction: TextInputAction.search,
      onChanged: _onQueryChanged,
      decoration: InputDecoration(
        labelText: 'Search escrows',
        prefixIcon: const Icon(Icons.search),
        suffixIcon: _query.isEmpty
            ? null
            : IconButton(
                tooltip: 'Clear',
                icon: const Icon(Icons.clear),
                onPressed: () {
                  _searchCtrl.clear();
                  _onQueryChanged('');
                },
              ),
      ),
    );
  }

  Widget _buildFilters(ThemeData theme) {
    final statuses = <String, String>{
      'all': 'All statuses',
      'pending': 'Pending',
      'funded': 'Funded',
      'released': 'Released',
      'refunded': 'Refunded',
      'cancelled': 'Cancelled',
    };

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      crossAxisAlignment: WrapCrossAlignment.center,
      children: [
        for (final entry in statuses.entries)
          ChoiceChip(
            label: Text(entry.value),
            selected: _statusFilter == entry.key,
            onSelected: (selected) {
              if (!selected) return;
              setState(() {
                _statusFilter = entry.key;
              });
            },
          ),
        FilterChip(
          label: const Text('Show closed escrows'),
          selected: _includeClosed,
          onSelected: (value) {
            setState(() {
              _includeClosed = value;
            });
          },
        ),
      ],
    );
  }

  Widget _buildEscrowCard(EscrowModel escrow) {
    final theme = Theme.of(context);
    final available = _formatCurrency(escrow, escrow.availableAmount);
    final funded = _formatCurrency(escrow, escrow.amount);
    final released = _formatCurrency(escrow, escrow.amountReleased);
    final refunded = _formatCurrency(escrow, escrow.amountRefunded);
    final dateFormatter = DateFormat.yMMMd().add_jm();

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
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
                        'Escrow #${escrow.id}',
                        style: theme.textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.w700),
                      ),
                      if (escrow.holdReference != null)
                        Text(
                          escrow.holdReference!,
                          style: theme.textTheme.bodySmall
                              ?.copyWith(color: theme.colorScheme.outline),
                        ),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: _statusColor(theme, escrow),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    _statusLabel(escrow.status),
                    style: theme.textTheme.labelMedium?.copyWith(
                      color: theme.colorScheme.onPrimaryContainer,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                _MetricTile(label: 'Funded', value: funded, icon: Icons.savings),
                _MetricTile(
                    label: 'Available',
                    value: available,
                    icon: Icons.lock_clock,
                    emphasize: true),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _MetricTile(label: 'Released', value: released, icon: Icons.outbond),
                _MetricTile(label: 'Refunded', value: refunded, icon: Icons.refresh),
              ],
            ),
            const SizedBox(height: 16),
            if (escrow.ledger.isNotEmpty)
              ExpansionTile(
                title: const Text('Ledger activity'),
                children: [
                  for (final entry in escrow.ledger)
                    ListTile(
                      leading: Icon(
                        entry.direction == 'debit'
                            ? Icons.south_east
                            : Icons.north_east,
                      ),
                      title: Text(entry.type.replaceAll('_', ' ')),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(dateFormatter.format(entry.occurredAt)),
                          if (entry.notes != null && entry.notes!.isNotEmpty)
                            Text(entry.notes!),
                        ],
                      ),
                      trailing: Text(
                        _formatCurrency(escrow, entry.amount),
                        style: theme.textTheme.bodyMedium?.copyWith(
                          fontFeatures: const [FontFeature.tabularFigures()],
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                ],
              ),
            if (['awaiting_funding', 'requires_action'].contains(escrow.status))
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: FilledButton.icon(
                  icon: _isFunding
                      ? const SizedBox(
                          height: 16,
                          width: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.lock_open),
                  label: Text(_isFunding ? 'Processing…' : 'Fund escrow'),
                  onPressed: _isFunding ? null : () => _fundEscrow(escrow),
                ),
              ),
            if (!escrow.isClosed && escrow.availableAmount > 0)
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.outbond),
                      label: const Text('Release funds'),
                      onPressed: () => _showActionSheet(escrow, _EscrowAction.release),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.refresh),
                      label: const Text('Refund'),
                      onPressed: () => _showActionSheet(escrow, _EscrowAction.refund),
                    ),
                  ),
                ],
              ),
            if (escrow.fundedAt != null)
              Padding(
                padding: const EdgeInsets.only(top: 12),
                child: Wrap(
                  spacing: 12,
                  runSpacing: 8,
                  children: [
                    Chip(
                      avatar: const Icon(Icons.schedule, size: 18),
                      label: Text('Funded ${dateFormatter.format(escrow.fundedAt!)}'),
                    ),
                    if (escrow.releasedAt != null)
                      Chip(
                        avatar: const Icon(Icons.task_alt, size: 18),
                        label: Text('Released ${dateFormatter.format(escrow.releasedAt!)}'),
                      ),
                    if (escrow.refundedAt != null)
                      Chip(
                        avatar: const Icon(Icons.verified, size: 18),
                        label: Text('Refunded ${dateFormatter.format(escrow.refundedAt!)}'),
                      ),
                    if (escrow.expiresAt != null)
                      Chip(
                        avatar: const Icon(Icons.timer_outlined, size: 18),
                        label: Text('Expires ${dateFormatter.format(escrow.expiresAt!)}'),
                      ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _MetricTile extends StatelessWidget {
  const _MetricTile({
    required this.label,
    required this.value,
    required this.icon,
    this.emphasize = false,
  });

  final String label;
  final String value;
  final IconData icon;
  final bool emphasize;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final textTheme = emphasize
        ? theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)
        : theme.textTheme.titleMedium;
    return Expanded(
      child: ListTile(
        dense: true,
        contentPadding: EdgeInsets.zero,
        leading: Icon(icon),
        title: Text(label, style: theme.textTheme.labelMedium),
        subtitle: Text(
          value,
          style: textTheme,
        ),
      ),
    );
  }
}
