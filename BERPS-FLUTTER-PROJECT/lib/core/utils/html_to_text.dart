import 'package:html/parser.dart' as html_parser;
import 'package:html/dom.dart' as html_dom;

/// Converts an HTML fragment into clean, editable plain text.
///
/// Notes created via the web rich-text editor arrive with markup like
/// `<p><a href="…">link</a></p><p><br></p><p>username: foo</p>`. Showing that
/// raw HTML inside a mobile TextField is unreadable, so we strip the tags and
/// preserve readable line breaks and link URLs.
///
/// - `<p>`, `<div>`, `<li>` → line breaks
/// - `<br>` → line break
/// - `<a href="url">text</a>` → `text (url)` when the visible text differs
///   from the href, otherwise just the text
/// - HTML entities (`&amp;`, `&lt;`, `&nbsp;` …) are decoded
/// - All other tags are removed, their inner text kept
/// - Trailing/leading whitespace and excessive blank lines are collapsed
String htmlToPlainText(String html) {
  if (html.trim().isEmpty) return '';

  final doc = html_parser.parse(html);
  final buffer = StringBuffer();
  _serializeNode(doc.body ?? doc, buffer);
  var text = buffer.toString();
  if (text.isEmpty) text = doc.body?.text ?? '';
  return _collapseWhitespace(text).trim();
}

void _serializeNode(html_dom.Node node, StringBuffer out) {
  if (node is html_dom.Text) {
    out.write(node.text);
    return;
  }
  if (node is html_dom.Element) {
    final tag = node.localName?.toLowerCase() ?? '';
    switch (tag) {
      case 'br':
        out.write('\n');
        return;
      case 'script':
      case 'style':
        return;
    }
    for (final child in node.nodes) {
      _serializeNode(child, out);
    }
    switch (tag) {
      case 'p':
      case 'div':
      case 'li':
      case 'tr':
        out.write('\n');
        break;
      case 'a':
        final href = (node.attributes['href'] ?? '').trim();
        final inner = out.toString().split('\n').last.trim();
        if (href.isNotEmpty &&
            href != inner &&
            !inner.contains(href)) {
          out.write(' ($href)');
        }
        break;
    }
  }
}

String _collapseWhitespace(String input) {
  return input
      .replaceAll('\r\n', '\n')
      .replaceAll('\r', '\n')
      .replaceAll(RegExp(r'[ \t]+'), ' ')
      .replaceAll(RegExp(r'\n{3,}'), '\n\n');
}
