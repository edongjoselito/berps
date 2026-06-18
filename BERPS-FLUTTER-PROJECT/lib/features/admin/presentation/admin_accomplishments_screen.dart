import 'package:flutter/material.dart';
import 'package:phosphor_flutter/phosphor_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive.dart';
import '../../../core/widgets/skeleton.dart';
import '../../auth/domain/staff_session.dart';
import '../data/admin_api.dart';
import '../domain/admin_models.dart';
import 'admin_widgets.dart';

class AdminAccomplishmentsScreen extends StatefulWidget {
  const AdminAccomplishmentsScreen({super.key, required this.session});
  final StaffSession session;

  @override
  State<AdminAccomplishmentsScreen> createState() =>
      _AdminAccomplishmentsScreenState();
}

class _AdminAccomplishmentsScreenState
    extends State<AdminAccomplishmentsScreen> {
  final AdminApi _api = AdminApi();
  late Future<List<AdminAccomplishment>> _future;
  late int _month;
  late int _year;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _month = now.month;
    _year = now.year;
    _future = _load();
  }

  Future<List<AdminAccomplishment>> _load() => _api.fetchAccomplishments(
        baseUrl: widget.session.baseUrl,
        token: widget.session.token,
        month: _month,
        year: _year,
      );

  void _reload() => setState(() => _future = _load());

  @override
  Widget build(BuildContext context) {
    final gutter = context.gutter;
    return Scaffold(
      backgroundColor: AppTheme.background,
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () async {
          _reload();
          await _future;
        },
        child: FutureBuilder<List<AdminAccomplishment>>(
          future: _future,
          builder: (context, snapshot) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: EdgeInsets.fromLTRB(gutter, 12, gutter, 28),
              children: [
                AdminHeader(
                  title: 'Accomplishments',
                  subtitle: 'Closed tasks across the team',
                  onBack: () => Navigator.pop(context),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    MonthYearButton(
                      month: _month,
                      year: _year,
                      onChanged: (m, y) {
                        setState(() {
                          _month = m ?? _month;
                          _year = y ?? _year;
                          _future = _load();
                        });
                      },
                    ),
                    const Spacer(),
                    if (snapshot.hasData)
                      Text(
                        '${snapshot.data!.length} total',
                        style: const TextStyle(
                          fontWeight: FontWeight.w700,
                          fontSize: 12.5,
                          color: AppTheme.textSecondary,
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 16),
                if (snapshot.connectionState == ConnectionState.waiting)
                  ...List.generate(
                    4,
                    (_) => const Padding(
                      padding: EdgeInsets.only(bottom: 10),
                      child: Skeleton(height: 88, radius: 16),
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
                else if (snapshot.data!.isEmpty)
                  const Padding(
                    padding: EdgeInsets.only(top: 50),
                    child: AdminEmptyView(
                      icon: PhosphorIconsFill.trophy,
                      title: 'No accomplishments',
                      message: 'Nothing was closed in this period.',
                    ),
                  )
                else
                  ...snapshot.data!.map((a) => AccomplishmentCard(item: a)),
              ],
            );
          },
        ),
      ),
    );
  }
}
