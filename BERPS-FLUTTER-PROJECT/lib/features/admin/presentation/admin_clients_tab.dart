import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/haptics.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/app_toast.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class AdminClientsTab extends StatefulWidget {
  const AdminClientsTab({
    super.key,
    required this.session,
    required this.onMenu,
  });
  final StaffSession session;
  final VoidCallback onMenu;

  @override
  State<AdminClientsTab> createState() => _AdminClientsTabState();
}

class _AdminClientsTabState extends State<AdminClientsTab> {
  final AdminApi _api = AdminApi();
  late Future<AdminClientsData> _future;
  String _query = '';

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<AdminClientsData> _load() => _api.fetchClients(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
      );

  void _reload() => setState(() => _future = _load());

  Future<void> _openForm({AdminClient? client, String? nextId}) async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ClientFormSheet(
        session: widget.session,
        api: _api,
        existing: client,
        nextCustId: nextId,
      ),
    );
    if (saved == true && mounted) _reload();
  }

  Future<void> _confirmDelete(AdminClient client) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Delete client?'),
        content: Text(
          'This permanently removes “${client.customer}”. This cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    try {
      await _api.deleteClient(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        custId: client.custId,
      );
      if (!mounted) return;
      AppToast.success(context, 'Client deleted.');
      _reload();
    } on ApiException catch (e) {
      if (!mounted) return;
      AppToast.error(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return Scaffold(
      backgroundColor: AppTheme.background,
      floatingActionButton: FutureBuilder<AdminClientsData>(
        future: _future,
        builder: (context, snapshot) {
          return FloatingActionButton.extended(
            onPressed: () => _openForm(nextId: snapshot.data?.nextCustId),
            backgroundColor: AppTheme.primaryDark,
            icon: const Icon(PhosphorIconsBold.plus, color: Colors.white),
            label: const Text('Add client',
                style:
                    TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
          );
        },
      ),
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () async {
          _reload();
          await _future;
        },
        child: FutureBuilder<AdminClientsData>(
          future: _future,
          builder: (context, snapshot) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 96),
              children: [
                AdminHeader(
                  title: 'Clients',
                  subtitle: 'Customer directory',
                  onMenu: widget.onMenu,
                ),
                const SizedBox(height: 14),
                TextField(
                  onChanged: (v) => setState(() => _query = v.toLowerCase()),
                  decoration: InputDecoration(
                    hintText: 'Search clients…',
                    prefixIcon: const Icon(PhosphorIconsBold.magnifyingGlass,
                        size: 18),
                  ),
                ),
                const SizedBox(height: 14),
                if (snapshot.connectionState == ConnectionState.waiting)
                  ...List.generate(
                    5,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 10),
                      child: Skeleton(height: 72, radius: 16),
                    ),
                  )
                else if (snapshot.hasError)
                  Padding(
                    padding: const EdgeInsets.only(top: 50),
                    child: AdminErrorView(
                      message: snapshot.error.toString(),
                      onRetry: _reload,
                    ),
                  )
                else
                  ..._buildList(snapshot.data!),
              ],
            );
          },
        ),
      ),
    );
  }

  List<Widget> _buildList(AdminClientsData data) {
    final filtered = _query.isEmpty
        ? data.clients
        : data.clients
            .where((c) =>
                c.customer.toLowerCase().contains(_query) ||
                c.contactPerson.toLowerCase().contains(_query) ||
                c.custId.toLowerCase().contains(_query))
            .toList();

    if (filtered.isEmpty) {
      return const [
        Padding(
          padding: EdgeInsets.only(top: 50),
          child: AdminEmptyView(
            icon: PhosphorIconsFill.usersThree,
            title: 'No clients found',
          ),
        ),
      ];
    }
    return filtered.map(_clientCard).toList();
  }

  Widget _clientCard(AdminClient c) {
    final stat = c.clientStat.trim();
    final statColor = stat.toLowerCase() == 'active'
        ? AppTheme.success
        : (stat.toLowerCase() == 'prospect'
            ? AppTheme.warning
            : AppTheme.textMuted);
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.border),
        boxShadow: AppTheme.shadowSoft,
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        onTap: () => _openForm(client: c),
        leading: Container(
          width: 44,
          height: 44,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: AppTheme.primarySoft,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Text(
            c.customer.isNotEmpty ? c.customer[0].toUpperCase() : '?',
            style: const TextStyle(
              fontWeight: FontWeight.w900,
              fontSize: 18,
              color: AppTheme.primaryDark,
            ),
          ),
        ),
        title: Text(
          c.customer,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            fontWeight: FontWeight.w800,
            fontSize: 14.5,
            color: AppTheme.textPrimary,
          ),
        ),
        subtitle: Text(
          [
            if (c.contactPerson.isNotEmpty) c.contactPerson,
            if (c.contact.isNotEmpty) c.contact,
          ].join(' · '),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(fontSize: 12, color: AppTheme.textSecondary),
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: statColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text(
                stat.isEmpty ? '—' : stat,
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w800,
                  color: statColor,
                ),
              ),
            ),
            IconButton(
              icon: const Icon(PhosphorIconsBold.trash,
                  size: 18, color: AppTheme.danger),
              onPressed: () {
                Haptics.light();
                _confirmDelete(c);
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _ClientFormSheet extends StatefulWidget {
  const _ClientFormSheet({
    required this.session,
    required this.api,
    this.existing,
    this.nextCustId,
  });

  final StaffSession session;
  final AdminApi api;
  final AdminClient? existing;
  final String? nextCustId;

  @override
  State<_ClientFormSheet> createState() => _ClientFormSheetState();
}

class _ClientFormSheetState extends State<_ClientFormSheet> {
  late final TextEditingController _name;
  late final TextEditingController _contactPerson;
  late final TextEditingController _contact;
  late final TextEditingController _address;
  late final TextEditingController _email;
  late final TextEditingController _source;
  late final TextEditingController _notes;
  String _stat = 'Active';
  bool _saving = false;

  bool get _isEdit => widget.existing != null;

  @override
  void initState() {
    super.initState();
    final c = widget.existing;
    _name = TextEditingController(text: c?.customer ?? '');
    _contactPerson = TextEditingController(text: c?.contactPerson ?? '');
    _contact = TextEditingController(text: c?.contact ?? '');
    _address = TextEditingController(text: c?.address ?? '');
    _email = TextEditingController(text: c?.clientEmail ?? '');
    _source = TextEditingController(text: c?.clientSource ?? '');
    _notes = TextEditingController(text: c?.notes ?? '');
    final s = (c?.clientStat ?? 'Active').trim();
    _stat = ['Active', 'Prospect', 'Inactive'].contains(s) ? s : 'Active';
  }

  @override
  void dispose() {
    _name.dispose();
    _contactPerson.dispose();
    _contact.dispose();
    _address.dispose();
    _email.dispose();
    _source.dispose();
    _notes.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_name.text.trim().isEmpty) {
      AppToast.warning(context, 'Client/company name is required.');
      return;
    }
    setState(() => _saving = true);
    final payload = {
      'Customer': _name.text.trim(),
      'ContactPerson': _contactPerson.text.trim(),
      'Contact': _contact.text.trim(),
      'Address': _address.text.trim(),
      'client_email': _email.text.trim(),
      'client_source': _source.text.trim(),
      'notes': _notes.text.trim(),
      'ClientStat': _stat,
    };
    try {
      if (_isEdit) {
        payload['CustID'] = widget.existing!.custId;
        await widget.api.updateClient(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          payload: payload,
        );
      } else {
        await widget.api.createClient(
          baseUrl: widget.session.baseUrl,
          token: widget.session.token,
          payload: payload,
        );
      }
      if (!mounted) return;
      AppToast.success(context, _isEdit ? 'Client updated.' : 'Client added.');
      Navigator.pop(context, true);
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      AppToast.error(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return Padding(
      padding: EdgeInsets.only(bottom: bottom),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(26)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 42,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.border,
                    borderRadius: BorderRadius.circular(99),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                _isEdit ? 'Edit client' : 'Add client',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 20,
                  color: AppTheme.textPrimary,
                ),
              ),
              if (!_isEdit && (widget.nextCustId ?? '').isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(
                  'New ID: ${widget.nextCustId}',
                  style: TextStyle(
                    fontSize: 12.5,
                    color: AppTheme.textSecondary,
                  ),
                ),
              ],
              const SizedBox(height: 16),
              _field('Client / company name', _name,
                  capitalize: true),
              _field('Contact person', _contactPerson, capitalize: true),
              _field('Contact number', _contact,
                  keyboard: TextInputType.phone),
              _field('Address', _address, capitalize: true),
              _field('Email', _email, keyboard: TextInputType.emailAddress),
              _field('Source', _source, capitalize: true),
              _label('Status'),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceMuted,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppTheme.border),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _stat,
                    isExpanded: true,
                    items: const [
                      DropdownMenuItem(value: 'Active', child: Text('Active')),
                      DropdownMenuItem(
                          value: 'Prospect', child: Text('Prospect')),
                      DropdownMenuItem(
                          value: 'Inactive', child: Text('Inactive')),
                    ],
                    onChanged: (v) => setState(() => _stat = v ?? 'Active'),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              _field('Notes', _notes, capitalize: true, maxLines: 3),
              const SizedBox(height: 18),
              FilledButton(
                onPressed: _saving ? null : _submit,
                child: _saving
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.4,
                          color: Colors.white,
                        ),
                      )
                    : Text(_isEdit ? 'Save changes' : 'Add client'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _label(String text) => Padding(
        padding: const EdgeInsets.only(bottom: 6),
        child: Text(
          text,
          style: TextStyle(
            fontWeight: FontWeight.w700,
            fontSize: 13,
            color: AppTheme.textSecondary,
          ),
        ),
      );

  Widget _field(
    String label,
    TextEditingController ctrl, {
    bool capitalize = false,
    TextInputType? keyboard,
    int maxLines = 1,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _label(label),
          TextField(
            controller: ctrl,
            keyboardType: keyboard,
            maxLines: maxLines,
            textCapitalization: capitalize
                ? TextCapitalization.words
                : TextCapitalization.none,
          ),
        ],
      ),
    );
  }
}
