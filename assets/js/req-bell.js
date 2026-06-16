(function () {
  if (window.__REQ_BELL_INIT) return;
  window.__REQ_BELL_INIT = true;

  var POLL_MS = 5000;
  var INCLUDE_PROCESSING = false;

  function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function fmtDate(s) {
    if (!s) return "";
    try {
      return new Date(s.replace(" ", "T")).toLocaleString();
    } catch (e) {
      return s;
    }
  }

  function renderItem(baseHref, row) {
    var title = escapeHtml(row.document_type || "Task update");
    var rawName = row.student || "Unknown";
    var timestamp = fmtDate(row.request_date || "");
    var href = (row.link && row.link.length) ? row.link : baseHref || "#";
    var safeHref = escapeHtml(href);
    var metaText = "By " + rawName + (timestamp ? " • " + timestamp : "");
    var meta = escapeHtml(metaText);
    var classes = "req-item";
    if (!row.is_seen) {
      classes += " req-item-unread";
    }
    var message = "";
    if (row.message) {
      message = '<div class="req-body">' + escapeHtml(row.message) + "</div>";
    }

    return [
      '<a class="',
      classes,
      '" href="',
      safeHref,
      '">',
      '<div class="req-title">',
      title,
      "</div>",
      message,
      '<div class="req-meta">',
      "<span>",
      meta,
      "</span>",
      "</div>",
      "</a>",
    ].join("");
  }

  function refreshCount($b) {
    var url = $b.data("count-url");
    if (!url) return;
    $.getJSON(url, { include_processing: INCLUDE_PROCESSING ? 1 : 0 }).done(function (resp) {
      var n = Number((resp && resp.count) || 0);
      var $badge = $b.find(".req-badge");
      var $title = $b.find(".req-title");
      if (n > 0) {
        $badge.text(n).show();
      } else {
        $badge.hide().text("0");
      }
      if ($title.length) {
        var baseLabel = $title.data("default") || "Notifications";
        $title.text(n > 0 ? baseLabel + " (" + n + ")" : baseLabel);
      }
    });
  }

  function refreshList($b) {
    var url = $b.data("list-url");
    if (!url) return;

    $.getJSON(url, {
      include_processing: INCLUDE_PROCESSING ? 1 : 0,
      limit: 8,
    }).done(function (resp) {
      var rows = (resp && resp.data) || [];
      var $list = $b.find(".req-list");
      var $empty = $b.find(".req-empty");
      if (!rows.length) {
        $list.empty();
        $empty.show();
        var $title = $b.find(".req-title");
        if ($title.length) {
          var baseLabel = $title.data("default") || "Notifications";
          $title.text(baseLabel);
        }
        return;
      }
      $empty.hide();
      var baseHref = $b.data("index-url") || "#";
      var html = rows
        .map(function (r) {
          return renderItem(baseHref, r);
        })
        .join("");
      $list.html(html);
    });
  }

  function markSeen($b) {
    var mark = $b.data("markseen-url");
    if (!mark) return;
    $.post(mark).always(function () {
      $b.find(".req-badge").text("0").hide();
      $b.find(".req-list .req-item").removeClass("req-item-unread");
    });
  }

  function tick() {
    $(".req-bell").each(function () {
      var $b = $(this);
      refreshCount($b);
      refreshList($b);
    });
  }

  $(function () {
    tick();
    setInterval(tick, POLL_MS);

    $(document).on("shown.bs.dropdown", ".req-bell", function () {
      var $b = $(this);
      refreshCount($b);
      refreshList($b);
    });

    $(document).on("click", ".req-bell .req-list a", function () {
      var $link = $(this);
      $link.removeClass("req-item-unread");
      var $b = $link.closest(".req-bell");
      markSeen($b);
    });

    $(document).on("click", ".req-bell .req-view-all", function () {
      var $b = $(this).closest(".req-bell");
      markSeen($b);
    });
  });
})();
