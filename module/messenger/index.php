<?php
require __DIR__ . '/common.php';
$me = ms_identity();
if (!$me) { echo '<div class="alert alert-danger">Please log in to use the messenger.</div>'; return; }
$who = ms_person($me[0], $me[1]);
include __DIR__ . '/style.php';
?>
<section class="content"><div class="container-fluid">
<div class="card ss-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title"><i class="fas fa-comments"></i> Messenger</h3>
        <button type="button" class="btn ss-btn light" id="msNewGroup"><i class="fas fa-users"></i> New Group</button>
    </div>
    <div class="card-body">
        <div class="ms-wrap" id="msWrap">

            <!-- left: conversations -->
            <div class="ms-side">
                <div class="ms-search"><div class="ss-input"><i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="msSearch" placeholder="Search people or groups"></div></div>
                <div class="ms-tabs">
                    <a class="active" data-f="all">All</a><a data-f="person">People</a><a data-f="group">Groups</a><a data-f="unread">Unread</a>
                </div>
                <div class="ms-list" id="msList"></div>
            </div>

            <!-- right: chat -->
            <div class="ms-main">
                <div class="ms-empty" id="msEmpty"><i class="fas fa-comment-dots"></i><h5>Your messages</h5><span>Select a person or group to start chatting.</span></div>

                <div id="msChat" style="display:none;flex:1;flex-direction:column;min-height:0">
                    <div class="ms-head">
                        <span class="ms-back" id="msBack"><i class="fas fa-arrow-left"></i></span>
                        <div class="ms-av" id="msHAv"><span></span><i class="dot"></i></div>
                        <div><b id="msHName"></b><small id="msHSub"></small></div>
                    </div>
                    <div class="ms-thread" id="msThread"></div>
                    <div class="ms-composer">
                        <div class="ms-emoji" id="msEmoji"></div>
                        <div class="ms-chip" id="msReplyBar"><span><i class="fas fa-reply"></i> <b id="msRName"></b> <span id="msRText"></span></span><i class="fas fa-times x" id="msRX"></i></div>
                        <div class="ms-chip" id="msChip"><span><i class="fas fa-paperclip"></i> <span id="msChipName"></span></span><i class="fas fa-times x" id="msChipX"></i></div>
                        <div class="ms-bar">
                            <span class="ms-tool" id="msEmojiBtn" title="Emoji"><i class="far fa-smile"></i></span>
                            <span class="ms-tool" id="msAttach" title="Attach file"><i class="fas fa-paperclip"></i></span>
                            <input type="file" id="msFile" style="display:none">
                            <textarea id="msBody" rows="1" placeholder="Type a message..."></textarea>
                            <button type="button" class="ms-send" id="msSend"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div></section>

<div class="ms-react-pop" id="msReactPop"><span>👍</span><span>❤️</span><span>😂</span><span>😮</span><span>😢</span><span>🙏</span></div>

<!-- New group modal -->
<div class="modal fade ss-modal" id="msGroupModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title"><span class="ss-badge"><i class="fas fa-users"></i></span>New Group</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <div class="ss-group"><i class="fas fa-info-circle"></i>Group Details</div>
        <div class="form-group"><label>Group Name</label>
            <div class="ss-input"><i class="fas fa-tag"></i><input type="text" class="form-control" id="msGName" maxlength="100" placeholder="e.g. Clinic Team"></div></div>
        <div class="ss-group"><i class="fas fa-user-plus"></i>Members</div>
        <div class="ms-pick" id="msPick"></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="msGCreate"><i class="fas fa-check"></i> Create Group</button>
    </div>
  </div></div>
</div>

<script>
(function boot(){ if (!window.jQuery) return setTimeout(boot, 50);
(function ($) {
    var API = <?= json_encode(ms_base_url() . '/ajax.php') ?>;
    var replyTo = 0, store = {}, reactTarget = 0;
    var conv = null, lastId = 0, contacts = [], filter = 'all', busy = false, lastDay = '', seenUpTo = 0, isGroup = false;

    function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function ini(n){ return (n || '?').trim().split(/\s+/).slice(0,2).map(function(x){ return x.charAt(0); }).join('').toUpperCase(); }
    function hm(d){ return d.toLocaleTimeString([], {hour:'numeric', minute:'2-digit'}); }
    function stamp(t){
        if (!t) return ''; var d = new Date(t.replace(' ', 'T')), n = new Date();
        return d.toDateString() === n.toDateString() ? hm(d) : d.toLocaleDateString([], {month:'short', day:'numeric'});
    }
    function dayLabel(d){
        var n = new Date(), y = new Date(); y.setDate(n.getDate() - 1);
        if (d.toDateString() === n.toDateString()) return 'Today';
        if (d.toDateString() === y.toDateString()) return 'Yesterday';
        return d.toLocaleDateString([], {weekday:'short', month:'long', day:'numeric', year:'numeric'});
    }
    function api(action, data, files){
        var fd = new FormData(); fd.append('action', action);
        Object.keys(data || {}).forEach(function(k){ var v = data[k];
            if (Array.isArray(v)) v.forEach(function(x){ fd.append(k + '[]', x); }); else fd.append(k, v); });
        if (files) Object.keys(files).forEach(function(k){ fd.append(k, files[k]); });
        return fetch(API, {method:'POST', body:fd, credentials:'same-origin'}).then(function(r){ return r.json(); });
    }

    /* ---------- conversation list ---------- */
    function renderList(){
        var s = $('#msSearch').val().toLowerCase(), h = '';
        contacts.forEach(function(c){
            if (filter === 'person' && c.kind !== 'person') return;
            if (filter === 'group'  && c.kind !== 'group')  return;
            if (filter === 'unread' && !c.unread) return;
            if (s && (c.name + ' ' + c.role).toLowerCase().indexOf(s) < 0) return;
            h += '<div class="ms-item' + (c.key === conv ? ' active' : '') + '" data-k="' + c.key + '">' +
                 '<div class="ms-av ' + c.kind + (c.online ? ' on' : '') + '">' + (c.kind === 'group' ? '<i class="fas fa-users"></i>' : esc(ini(c.name))) + '<i class="dot"></i></div>' +
                 '<div class="ms-info"><div class="top"><b>' + esc(c.name) + '</b><time>' + stamp(c.time) + '</time></div>' +
                 '<div class="bot"><span class="pv">' + (c.preview ? esc(c.preview) : '<span class="ms-role">' + esc(c.role) + '</span>') + '</span>' +
                 (c.unread ? '<span class="ms-badge">' + c.unread + '</span>' : '') + '</div></div></div>';
        });
        $('#msList').html(h || '<div class="ms-empty small"><i class="fas fa-inbox"></i>Nothing here yet</div>');
    }
    function loadContacts(){
        return api('contacts').then(function(r){
            if (r.error) { $('#msList').html('<div class="ms-empty small"><i class="fas fa-exclamation-triangle"></i>' + esc(r.error) + '</div>'); return; }
            contacts = r.contacts; renderList();
            if (conv) { var c = contacts.filter(function(x){ return x.key === conv; })[0]; if (c) header(c); }
        }).catch(function(){
            $('#msList').html('<div class="ms-empty small"><i class="fas fa-exclamation-triangle"></i>Cannot reach ajax.php (' + esc(API) + ')</div>');
        });
    }
    function header(c){
        isGroup = c.kind === 'group';
        $('#msHName').text(c.name);
        $('#msHAv').attr('class', 'ms-av ' + c.kind + (c.online ? ' on' : '')).find('span').html(isGroup ? '<i class="fas fa-users"></i>' : esc(ini(c.name)));
        $('#msHSub').text(isGroup ? c.role : c.role + (c.online ? ' · Active now' : ''));
    }

    /* ---------- thread ---------- */
    function bubble(m){
        var day = new Date(m.time), dl = day.toDateString(), h = '', acts = '';
        if (dl !== lastDay) { h += '<div class="ms-day">' + dayLabel(day) + '</div>'; lastDay = dl; }
        store[m.id] = {sender: m.sender, text: m.body || (m.file ? '📎 ' + m.file.name : '')};
        var inner;
        if (m.deleted) inner = '<div class="ms-bub gone"><i class="fas fa-ban"></i> Message removed</div>';
        else {
            inner = '<div class="ms-bub">' + (isGroup && !m.mine ? '<span class="who">' + esc(m.sender) + '</span>' : '');
            if (m.reply) inner += '<div class="ms-quote" data-go="' + m.reply.id + '"><b>' + esc(m.reply.sender) + '</b><span>' + esc(m.reply.text) + '</span></div>';
            if (m.file) inner += m.file.image
                ? '<a href="' + esc(m.file.url) + '" target="_blank"><img class="pic" src="' + esc(m.file.url) + '"></a>'
                : '<a class="file" href="' + esc(m.file.url) + '" target="_blank" download><i class="fas fa-file-alt"></i>' + esc(m.file.name) + '</a>';
            if (m.body) inner += esc(m.body);
            inner += '<span class="meta">' + hm(day) + (m.mine && !isGroup ? ' <i class="fas fa-check' + (m.id <= seenUpTo ? '-double' : '') + '" data-seen="' + m.id + '"></i>' : '') + '</span>';
            inner += '<div class="ms-rx"></div></div>';
            acts = '<div class="ms-acts"><i class="far fa-smile ms-react" title="React"></i><i class="fas fa-reply ms-reply" title="Reply"></i>' +
                   (m.mine ? '<i class="fas fa-trash-alt ms-del" title="Remove"></i>' : '') + '</div>';
        }
        return h + '<div class="ms-row' + (m.mine ? ' mine' : '') + '" data-id="' + m.id + '">' + (m.mine ? acts + inner : inner + acts) + '</div>';
    }
    function applyRx(map){
        $('#msThread .ms-row').each(function(){
            var row = $(this), list = map[row.data('id')] || [], html = list.map(function(x){
                return '<span class="chip' + (x.mine ? ' me' : '') + '" data-e="' + x.e + '">' + x.e + (x.n > 1 ? '<b>' + x.n + '</b>' : '') + '</span>';
            }).join('');
            if (row.data('rx') !== html) { row.data('rx', html); row.find('.ms-rx').html(html); }
        });
    }
    function setReply(id){
        var r = store[id]; if (!r) return; replyTo = id;
        $('#msRName').text(r.sender); $('#msRText').text(r.text); $('#msReplyBar').css('display', 'flex'); $('#msBody').focus();
    }
    function clearReply(){ replyTo = 0; $('#msReplyBar').hide(); }
    function nearBottom(){ var t = $('#msThread')[0]; return t.scrollHeight - t.scrollTop - t.clientHeight < 140; }
    function toBottom(){ var t = $('#msThread')[0]; t.scrollTop = t.scrollHeight; }

    function poll(first){
        if (!conv || busy) return; busy = true; var k = conv;
        api('messages', {conv: k, after: first ? 0 : lastId}).then(function(r){
            busy = false; if (k !== conv || !r.messages) return;
            var stick = first || nearBottom(), mineNew = false, h = '';
            seenUpTo = r.seen || seenUpTo;
            r.messages.forEach(function(m){ h += bubble(m); lastId = Math.max(lastId, m.id); if (m.mine) mineNew = true; });
            if (first) $('#msThread').html(h || '<div class="ms-empty small" style="margin:auto"><i class="fas fa-hand-sparkles"></i>Say hello!</div>');
            else if (h) { $('#msThread .ms-empty').remove(); $('#msThread').append(h); }
            (r.deleted || []).forEach(function(id){
                var row = $('.ms-row[data-id="' + id + '"]');
                if (row.length && !row.find('.gone').length) row.html('<div class="ms-bub gone"><i class="fas fa-ban"></i> Message removed</div>');
            });
            $('#msThread [data-seen]').each(function(){ if (+$(this).data('seen') <= seenUpTo) $(this).attr('class', 'fas fa-check-double'); });
            applyRx(r.reactions || {});
            if (stick || mineNew) toBottom();
            if (h && !first) loadContacts();
        }).catch(function(){ busy = false; });
    }

    function open(k){
        conv = k; lastId = 0; lastDay = ''; seenUpTo = 0; store = {}; clearReply();
        var c = contacts.filter(function(x){ return x.key === k; })[0]; if (c) header(c);
        $('#msEmpty').hide(); $('#msChat').css('display', 'flex'); $('#msWrap').addClass('chat');
        $('#msThread').empty(); clearFile(); $('#msBody').val('').focus();
        renderList(); poll(true); setTimeout(loadContacts, 600);
    }

    /* ---------- sending ---------- */
    function clearFile(){ $('#msFile').val(''); $('#msChip').hide(); }
    function send(){
        var body = $.trim($('#msBody').val()), f = $('#msFile')[0].files[0];
        if (!conv || (!body && !f)) return;
        $('#msSend').prop('disabled', true);
        api('send', {conv: conv, body: body, reply_to: replyTo || ''}, f ? {file: f} : null).then(function(r){
            $('#msSend').prop('disabled', false);
            if (r.error) { alert(r.error); return; }
            $('#msBody').val('').css('height', 'auto').focus(); clearFile(); clearReply(); poll(false); loadContacts();
        }).catch(function(){ $('#msSend').prop('disabled', false); alert('Could not send. Please try again.'); });
    }

    /* ---------- events ---------- */
    $('#msList').on('click', '.ms-item', function(){ open($(this).data('k')); });
    $('#msSearch').on('input', renderList);
    $('.ms-tabs').on('click', 'a', function(){ $('.ms-tabs a').removeClass('active'); $(this).addClass('active'); filter = $(this).data('f'); renderList(); });
    $('#msBack').on('click', function(){ $('#msWrap').removeClass('chat'); });
    $('#msSend').on('click', send);
    $('#msBody').on('keydown', function(e){ if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } })
                .on('input', function(){ this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 120) + 'px'; });
    $('#msAttach').on('click', function(){ $('#msFile').click(); });
    $('#msFile').on('change', function(){ var f = this.files[0]; if (!f) return clearFile(); $('#msChipName').text(f.name); $('#msChip').css('display', 'flex'); });
    $('#msChipX').on('click', clearFile);
    $('#msThread').on('click', '.ms-del', function(){
        var id = $(this).closest('.ms-row').data('id');
        if (confirm('Remove this message for everyone?')) api('delete', {id: id}).then(function(){ lastDay = ''; poll(true); loadContacts(); });
    });

    /* reactions + replies */
    $('#msThread').on('click', '.ms-react', function(e){
        e.stopPropagation(); reactTarget = $(this).closest('.ms-row').data('id');
        var b = this.getBoundingClientRect(), pop = $('#msReactPop').css('display', 'block');
        var left = Math.max(8, Math.min(window.innerWidth - pop.outerWidth() - 8, b.left - 20));
        pop.css({left: left, top: Math.max(8, b.top - 52)});
    });
    $('#msReactPop').on('click', 'span', function(e){
        e.stopPropagation(); $('#msReactPop').hide();
        api('react', {id: reactTarget, emoji: $(this).text()}).then(function(){ poll(false); });
    });
    $('#msThread').on('click', '.ms-rx .chip', function(){
        api('react', {id: $(this).closest('.ms-row').data('id'), emoji: $(this).data('e')}).then(function(){ poll(false); });
    });
    $('#msThread').on('click', '.ms-reply', function(){ setReply($(this).closest('.ms-row').data('id')); });
    $('#msThread').on('click', '.ms-quote', function(){
        var row = $('.ms-row[data-id="' + $(this).data('go') + '"]');
        if (!row.length) return;
        row[0].scrollIntoView({behavior: 'smooth', block: 'center'}); row.addClass('flash'); setTimeout(function(){ row.removeClass('flash'); }, 1300);
    });
    $('#msRX').on('click', clearReply);
    $('#msBody').on('keydown', function(e){ if (e.key === 'Escape') clearReply(); });
    $('#msThread').on('scroll', function(){ $('#msReactPop').hide(); });

    var emo = '😀😁😂🤣😊😍😘😎🤔😢😭😡👍👎👏🙏💪🎉❤️🔥✨✅❌📌📎📚🏫🩺💊🙂😴🤝'.match(/\p{Extended_Pictographic}(\uFE0F)?/gu);
    $('#msEmoji').html(emo.map(function(e){ return '<span>' + e + '</span>'; }).join(''));
    $('#msEmojiBtn').on('click', function(e){ e.stopPropagation(); var b = $('#msEmoji'); b.css('display', b.css('display') === 'none' ? 'flex' : 'none'); });
    $('#msEmoji').on('click', 'span', function(){ var t = $('#msBody')[0]; t.value += $(this).text(); t.focus(); });
    $(document).on('click', function(){ $('#msEmoji').hide(); $('#msReactPop').hide(); });

    /* ---------- group modal ---------- */
    $('#msNewGroup').on('click', function(){
        var h = '';
        contacts.filter(function(c){ return c.kind === 'person'; }).sort(function(a, b){ return a.name.localeCompare(b.name); }).forEach(function(c){
            h += '<label><input type="checkbox" value="' + c.key + '"> ' + esc(c.name) + '<small>' + esc(c.role) + '</small></label>';
        });
        $('#msPick').html(h || '<div class="ms-empty small">No people available</div>'); $('#msGName').val('');
        $('#msGroupModal').modal('show');
    });
    $('#msGCreate').on('click', function(){
        var m = $('#msPick input:checked').map(function(){ return this.value; }).get();
        api('create_group', {name: $('#msGName').val(), members: m}).then(function(r){
            if (r.error) return alert(r.error);
            $('#msGroupModal').modal('hide'); loadContacts().then(function(){ open(r.key); });
        });
    });

    /* ---------- start + polling ---------- */
    loadContacts();
    setInterval(function(){ if (!document.hidden) poll(false); }, 3000);
    setInterval(function(){ if (!document.hidden) loadContacts(); }, 8000);
})(jQuery);
})();
</script>