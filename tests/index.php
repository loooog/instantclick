<?php
$pages = [
  'Index page' => 'index',
  'Page with anchors #1' => 'anchor1',
  'Page with anchors #2' => 'anchor2',
  'Page without title' => 'no-title',
  'Minimal markup' => 'minimal',
  'Entities in the &#8249;title&rsaquo;' => 'entities',
  'Noscript' => 'noscript',
  'Alter on receive' => 'alter-receive',
  'Childs and blacklist' => 'child-n-blacklist',
  'Non-HTML file' => 'non-html',
  'Touch events' => 'touch',
  'Event delegation' => 'event-delegation',
];

if (isset($_GET['page']) && in_array($_GET['page'], $pages)) {
  $page = $_GET['page'];
}
else {
  $page = 'index';
}

$delays = [200, 500, 1000, 5000];

if (isset($_GET['on'])) {
  if (in_array($_GET['on'], ['default', 'mousedown'])) {
    $preload_on = $_GET['on'];
  }
  else {
    $preload_on = (int)$_GET['on'];
  }
}
else {
  $preload_on = 'default';
}

$nocache = '&amp;nocache=' . microtime(true) * 10000;
$append = '&amp;on=' . $preload_on . $nocache;

if (isset($_GET['wait'])) {
  usleep((int)$_GET['wait'] * 1000);
}
?>
<!doctype html>
<meta charset="utf-8">
<?php if ($page != 'no-title'): ?>
<title>
<?php if ($page == 'entities'): ?>
Entities in the &#8249;title&rsaquo;
<?php else: ?>
<?= date('H : i : s') ?> . <?php printf("%03d", microtime() * 1000) ?>
<?php endif ?>
</title>
<?php endif ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css?<?= filemtime('style.css') ?>" data-instant-track>

<?php // Minimal specific code
if ($page == 'minimal'): ?>
<body>Hiya.
<?php exit;
endif ?>

<div id="preloading-level">
  <a data-no-instant href="?<?= $nocache ?>" class="<?= $preload_on === 'default' ? 'selected' : '' ?>">↻ Default</a>
  <a data-no-instant href="?on=0<?= $nocache ?>" class="<?= $preload_on === 0 ? 'selected' : '' ?>">↻ 0 ms</a>
  <a data-no-instant href="?on=1000<?= $nocache ?>" class="<?= $preload_on === 1000 ? 'selected' : '' ?>">↻ 1000 ms</a>
  <a data-no-instant href="?on=mousedown<?= $nocache ?>" class="<?= $preload_on === 'mousedown' ? 'selected' : '' ?>">↻ Mousedown</a>
</div>

<hr>

<table>
  <tr>
    <th>Page</th>
    <th colspan="<?= count($delays) ?>">Delays (in milliseconds)</th>

<?php foreach ($pages as $name => $row): ?>
  <tr>
    <td><a href="?page=<?= $row . $append ?>"><?= $name ?></a>
<?php foreach ($delays as $delay): ?>
    <td><a href="?page=<?= $row ?>&amp;wait=<?= $delay . $append ?>"><small><?= $delay ?></small></a>
<?php endforeach;
endforeach ?>
</table>

<hr>

<?php include('pages/' . $page . '.html') ?>

<div id="divDebug"></div>


<script src="instantclick.js.php?<?= filemtime('../src/instantclick.js') + filemtime('../src/loading-indicator.js') ?>" data-instant-track></script>


<script data-instant-track>
var $debugMessages = ''

function addDebugMessage(message) {
  var divDebug = document.getElementById('divDebug')
  if (!divDebug) {
    return
  }
  $debugMessages = message + '<br>' + (!divDebug.innerHTML && $debugMessages ? '<hr>' : '') + $debugMessages
  divDebug.innerHTML = $debugMessages
}

InstantClick.on('preload', function() {
  addDebugMessage('<small><small>Event: preload</small></small>')
})

InstantClick.on('receive', function(url, body, title) {
  if (url.indexOf('#alter') > -1) {
    addDebugMessage('<small><small>Event: receive (altered)</small></small>')
    var elementToAlter = body.querySelector('#to_alter')
    if (elementToAlter) {
      elementToAlter.innerHTML = '<b>Altered!</b>'
    }
    title = '[Altered] ' + title

    return {
      body: body,
      title: title
    }
  }
  addDebugMessage('<small><small>Event: receive</small></small>')
})

InstantClick.on('wait', function() {
  addDebugMessage('Event: wait')
})

InstantClick.on('restore', function() {
  addDebugMessage('Event: restore')
})

InstantClick.on('exit', function(url, status) {
  addDebugMessage('Event: exit (' + status + ')')
})

InstantClick.on('change', function(isInitialLoad) {
  if (!instantClick.supported) {
    addDebugMessage('<span style="color: crimson">Unsupported</span>')
    return
  }
  addDebugMessage('Event: change' + (isInitialLoad ? ' (initial load)' : ''))
  if (!isInitialLoad) {
    document.documentElement.setAttribute('instantclick-has-displayed', '')
  }
})

InstantClick.init(<?php
if ($preload_on === 'mousedown') {
  echo "'mousedown'";
}
elseif ($preload_on !== 'default') {
  echo $preload_on;
}
?>)

if (document.querySelectorAll) {
  var scriptsQueued = document.querySelectorAll('script[type="queued"]')
  for (var i = 0; i < scriptsQueued.length; i++) {
    eval(scriptsQueued[i].innerHTML)
  }
}
</script>
