import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../config.dart';
import '../../models/feed_job_model.dart';
import '../../providers/app_pages_providers/feed/feed_job_detail_provider.dart';

class FeedJobDetailScreen extends StatefulWidget {
  const FeedJobDetailScreen({super.key, required this.jobId, this.initialJob});

  final int jobId;
  final FeedJobModel? initialJob;

  @override
  State<FeedJobDetailScreen> createState() => _FeedJobDetailScreenState();
}

class _FeedJobDetailScreenState extends State<FeedJobDetailScreen> {
  late final PageController _pageController;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(viewportFraction: 0.92);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<FeedJobDetailProvider>();
      provider.bootstrap(jobId: widget.jobId, initialJob: widget.initialJob);
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        title: Text(translations?.jobDetail ?? 'Job detail'),
        actions: [
          Consumer<FeedJobDetailProvider>(
            builder: (context, provider, child) {
              final bookmarked = provider.isBookmarked;
              return IconButton(
                tooltip: bookmarked
                    ? (translations?.removeBookmark ?? 'Remove bookmark')
                    : (translations?.bookmark ?? 'Bookmark'),
                onPressed: provider.job == null ? null : provider.toggleBookmark,
                icon: Icon(bookmarked ? Icons.bookmark : Icons.bookmark_border),
              );
            },
          ),
        ],
      ),
      body: SafeArea(
        child: Consumer<FeedJobDetailProvider>(
          builder: (context, provider, child) {
            if (provider.isLoading && provider.job == null) {
              return const Center(child: CircularProgressIndicator());
            }

            if (provider.hasError && provider.job == null) {
              return _ErrorView(
                message: provider.errorMessage ?? translations?.somethingWentWrong ?? 'Unable to load job details.',
                onRetry: () => provider.bootstrap(jobId: widget.jobId, initialJob: widget.initialJob),
              );
            }

            final job = provider.job;
            if (job == null) {
              return _ErrorView(
                message: translations?.noDataFound ?? 'Job could not be found.',
                onRetry: () => provider.bootstrap(jobId: widget.jobId, initialJob: widget.initialJob),
              );
            }

            return RefreshIndicator(
              onRefresh: provider.refresh,
              child: CustomScrollView(
                physics: const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics()),
                slivers: [
                  SliverToBoxAdapter(child: _buildHeader(context, job)),
                  SliverToBoxAdapter(child: _buildMetadata(context, job)),
                  SliverToBoxAdapter(child: _buildDescription(context, job)),
                  SliverToBoxAdapter(child: _buildAttachments(context, job)),
                  SliverToBoxAdapter(child: _buildLocation(context, job)),
                  SliverToBoxAdapter(child: _buildBids(context, job)),
                  const SliverPadding(padding: EdgeInsets.only(bottom: 120)),
                ],
              ),
            );
          },
        ),
      ),
      bottomNavigationBar: Consumer<FeedJobDetailProvider>(
        builder: (context, provider, child) {
          final job = provider.job;
          if (job == null || !provider.canSubmitProposal) {
            return const SizedBox.shrink();
          }
          return SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: provider.isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Icon(Icons.send),
                  label: Text(translations?.submitProposal ?? 'Submit proposal'),
                  onPressed: provider.isSubmitting
                      ? null
                      : () async {
                          await _showProposalSheet(context, provider);
                        },
                  style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildHeader(BuildContext context, FeedJobModel job) {
    final theme = Theme.of(context);
    final formatter = NumberFormat.simpleCurrency(name: job.currency ?? 'USD');
    final budget = job.finalBudget ?? job.initialBudget ?? 0;
    final createdAt = job.createdAt != null ? DateFormat.yMMMd().add_jm().format(job.createdAt!.toLocal()) : null;
    final booking = job.bookingDate != null ? DateFormat.yMMMMd().format(job.bookingDate!.toLocal()) : null;

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(job.title, style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _Chip(text: formatter.format(budget), icon: Icons.attach_money),
              if (job.durationValue != null && job.durationUnit != null)
                _Chip(text: '${job.durationValue} ${job.durationUnit}', icon: Icons.timer_outlined),
              if (job.requiredServicemen != null && job.requiredServicemen! > 0)
                _Chip(text: '${job.requiredServicemen} people needed', icon: Icons.groups_3_outlined),
              if (job.distanceKm != null)
                _Chip(text: '${job.distanceKm!.toStringAsFixed(1)} km away', icon: Icons.location_on_outlined),
              _Chip(text: job.status.toUpperCase(), icon: Icons.flag_circle_outlined),
            ],
          ),
          if (createdAt != null || booking != null) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                if (createdAt != null)
                  Expanded(
                    child: _DetailRow(
                      icon: Icons.calendar_today,
                      label: translations?.postedOn ?? 'Posted',
                      value: createdAt,
                    ),
                  ),
                if (booking != null)
                  Expanded(
                    child: _DetailRow(
                      icon: Icons.schedule_send,
                      label: translations?.preferredDate ?? 'Preferred date',
                      value: booking,
                    ),
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMetadata(BuildContext context, FeedJobModel job) {
    if (job.bidsSummary == null || job.bidsSummary!.isEmpty) {
      return const SizedBox.shrink();
    }
    final theme = Theme.of(context);
    final total = (job.bidsSummary?['total'] as num?)?.toInt();
    final average = job.bidsSummary?['average'] as num?;
    final lowest = job.bidsSummary?['lowest'] as num?;

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: theme.colorScheme.surfaceVariant,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _StatTile(label: translations?.totalBids ?? 'Total bids', value: total?.toString() ?? '--'),
              _StatTile(
                label: translations?.avgBid ?? 'Avg bid',
                value: average != null ? NumberFormat.compactCurrency(symbol: job.currency ?? r'$').format(average) : '--',
              ),
              _StatTile(
                label: translations?.lowestBid ?? 'Lowest',
                value: lowest != null ? NumberFormat.compactCurrency(symbol: job.currency ?? r'$').format(lowest) : '--',
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDescription(BuildContext context, FeedJobModel job) {
    final theme = Theme.of(context);
    final description = job.description ?? translations?.noDataFoundDesc ?? 'No description provided yet.';
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(translations?.description ?? 'Description', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          Text(description, style: theme.textTheme.bodyLarge),
        ],
      ),
    );
  }

  Widget _buildAttachments(BuildContext context, FeedJobModel job) {
    final attachments = job.attachments;
    if (attachments == null || attachments.isEmpty) {
      return const SizedBox.shrink();
    }
    return SizedBox(
      height: 220,
      child: PageView.builder(
        controller: _pageController,
        itemCount: attachments.length,
        itemBuilder: (context, index) {
          final attachment = attachments[index];
          final imageUrl = (attachment is Map<String, dynamic>) ? attachment['url'] as String? : attachment.toString();
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: imageUrl != null && imageUrl.isNotEmpty
                  ? CachedNetworkImage(
                      imageUrl: imageUrl,
                      fit: BoxFit.cover,
                      placeholder: (context, url) => const Center(child: CircularProgressIndicator()),
                      errorWidget: (context, url, error) => const _AttachmentFallback(),
                    )
                  : const _AttachmentFallback(),
            ),
          );
        },
      ),
    );
  }

  Widget _buildLocation(BuildContext context, FeedJobModel job) {
    final location = job.location;
    if (location == null || location.isEmpty) {
      return const SizedBox.shrink();
    }
    final address = location['address'] ?? location['formatted_address'] ?? location['city'];
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: DecoratedBox(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          color: theme.colorScheme.surfaceVariant,
        ),
        child: ListTile(
          leading: const Icon(Icons.location_on_outlined),
          title: Text(translations?.location ?? 'Location'),
          subtitle: Text(address?.toString() ?? translations?.noDataFound ?? 'N/A'),
        ),
      ),
    );
  }

  Widget _buildBids(BuildContext context, FeedJobModel job) {
    final bidsRaw = job.bidsSummary?['recent'] as List<dynamic>?;
    if (bidsRaw == null || bidsRaw.isEmpty) {
      return const SizedBox.shrink();
    }
    final theme = Theme.of(context);
    final formatter = NumberFormat.compactCurrency(symbol: job.currency ?? r'$');
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(translations?.recentBids ?? 'Recent proposals', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600)),
          const SizedBox(height: 12),
          ...bidsRaw.take(5).map((entry) {
            final map = Map<String, dynamic>.from(entry as Map);
            final amount = map['amount'] as num?;
            final providerData = map['provider'];
            final bidder = map['provider_name'] ??
                (providerData is Map<String, dynamic> ? providerData['name'] : null);
            final createdRaw = map['created_at'];
            final createdAt = createdRaw is String ? DateTime.tryParse(createdRaw) : null;
            final submittedAt =
                createdAt != null ? DateFormat.yMMMd().add_jm().format(createdAt.toLocal()) : null;
            return Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              child: ListTile(
                leading: CircleAvatar(child: Text(bidder != null ? bidder.toString().substring(0, 1).toUpperCase() : '?')),
                title: Text(bidder?.toString() ?? translations?.anonymous ?? 'Anonymous'),
                subtitle: submittedAt != null ? Text(submittedAt) : null,
                trailing: Text(amount != null ? formatter.format(amount) : '--'),
              ),
            );
          }),
        ],
      ),
    );
  }

  Future<void> _showProposalSheet(BuildContext context, FeedJobDetailProvider provider) async {
    final theme = Theme.of(context);
    final amountController = TextEditingController();
    final messageController = TextEditingController();
    final durationController = TextEditingController();
    final formKey = GlobalKey<FormState>();

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            left: 24,
            right: 24,
            top: 24,
          ),
          child: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(translations?.submitProposal ?? 'Submit proposal', style: theme.textTheme.titleLarge),
                const SizedBox(height: 16),
                TextFormField(
                  controller: amountController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: InputDecoration(
                    labelText: translations?.yourBid ?? 'Your bid amount',
                    prefixIcon: const Icon(Icons.attach_money),
                  ),
                  validator: (value) {
                    final text = value?.trim();
                    if (text == null || text.isEmpty) {
                      return translations?.validationRequired ?? 'This field is required.';
                    }
                    final parsed = double.tryParse(text);
                    if (parsed == null || parsed <= 0) {
                      return translations?.validationInvalidAmount ?? 'Enter a valid amount';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: durationController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: translations?.proposalDuration ?? 'Estimated duration (days)',
                    prefixIcon: const Icon(Icons.timelapse_outlined),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return null;
                    }
                    final parsed = int.tryParse(value);
                    if (parsed == null || parsed <= 0) {
                      return translations?.validationInvalidNumber ?? 'Enter a valid number of days';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: messageController,
                  minLines: 3,
                  maxLines: 5,
                  decoration: InputDecoration(
                    labelText: translations?.proposalMessage ?? 'Message to customer',
                    alignLabelWithHint: true,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (!formKey.currentState!.validate()) {
                        return;
                      }
                      final amount = double.parse(amountController.text.trim());
                      final durationDays = durationController.text.trim().isEmpty
                          ? null
                          : int.parse(durationController.text.trim());
                      final message = messageController.text.trim().isEmpty ? null : messageController.text.trim();
                      try {
                        await provider.submitBid(
                          amount: amount,
                          message: message,
                          durationDays: durationDays,
                        );
                        if (context.mounted) Navigator.of(context).pop();
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text(translations?.proposalSubmitted ?? 'Proposal submitted successfully.')),
                          );
                        }
                      } catch (error) {
                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(provider.errorMessage ?? translations?.somethingWentWrong ?? error.toString()),
                              backgroundColor: theme.colorScheme.error,
                            ),
                          );
                        }
                      }
                    },
                    child: Text(translations?.submit ?? 'Submit'),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({required this.text, required this.icon});

  final String text;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Chip(
      avatar: Icon(icon, size: 18, color: theme.colorScheme.primary),
      label: Text(text),
      backgroundColor: theme.colorScheme.surfaceVariant,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
    );
  }
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({required this.icon, required this.label, required this.value});

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Row(
      children: [
        Icon(icon, size: 18, color: theme.colorScheme.primary),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: theme.textTheme.labelMedium?.copyWith(color: theme.textTheme.bodySmall?.color)),
              Text(value, style: theme.textTheme.bodyMedium),
            ],
          ),
        ),
      ],
    );
  }
}

class _StatTile extends StatelessWidget {
  const _StatTile({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Text(value, style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
        const SizedBox(height: 4),
        Text(label, style: theme.textTheme.bodySmall),
      ],
    );
  }
}

class _AttachmentFallback extends StatelessWidget {
  const _AttachmentFallback();

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return DecoratedBox(
      decoration: BoxDecoration(color: theme.colorScheme.surfaceVariant),
      child: Center(
        child: Icon(Icons.image_not_supported_outlined, color: theme.colorScheme.onSurfaceVariant, size: 42),
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  const _ErrorView({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.report_problem_outlined, size: 48, color: theme.colorScheme.error),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center, style: theme.textTheme.bodyLarge),
            const SizedBox(height: 20),
            FilledButton(onPressed: onRetry, child: Text(translations?.retry ?? 'Retry')),
          ],
        ),
      ),
    );
  }
}
