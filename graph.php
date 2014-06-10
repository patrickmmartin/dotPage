<!DOCTYPE html>
<html>
<head>
<style type="text/css">
.title
{
font-weight:normal;color:#000000;letter-spacing:1pt;word-spacing:2pt;font-size:25px;text-align:center;font-family:arial, helvetica, sans-serif;line-height:1;margin:0px;padding:0px;
}
</style>

<title>Graphviz tool</title></head>
<script type="text/javascript">
  function selectText(containerid) {
  if (document.selection) {
    var range = document.body.createTextRange();
    range.moveToElementText(document.getElementById(containerid));
    range.select();
  }
  else if (window.getSelection) {
    var range = document.createRange();
    range.selectNode(document.getElementById(containerid));
    window.getSelection().addRange(range);
  }
}
</script>
<body>

<form action = "graph.php" method="post">
<h1 class="title">What do you want to diagram today?</h1>
<textarea name="query" rows="15" style="width: 100%;resize:both" spellcheck="false" >
<?php if (isset($_POST["query"])) echo $_POST["query"]; 
?>
</textarea>
<br/>
<input type="submit" value="Render"/>

Tip: use "layout=" in your graph to set the layout engine. Valid values are dot, neato, twopi, circo, fdp, sfdp, patchwork.

<br/>
</form>

<?php

function graphviz($match)
{
    
    $orig = $match;
    
    $pipespec = array(
        0 => array(
            "pipe",
            "r"
        ),
        1 => array(
            "pipe",
            "w"
        ),
        2 => array(
            "pipe",
            "w"
        )
    );
    
    $renderer = "dot";
    
    $proc = proc_open($renderer . ' -Tsvg', $pipespec, $pipes, NULL, NULL);
    
    fwrite($pipes[0], $match);
    fclose($pipes[0]);
    
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    
    if (!proc_close($proc)) {
        
        $output = preg_replace('/.*<svg width="[0-9]+pt" height="([0-9]+pt)"/s', '<svg style="max-height:$1;" ', $output);
        
        // this is essentially to strip comments and remove collisions in node ids in multiple SVG documents within the same document
        $output = preg_replace('/<!--(.*)-->/Uis', '', $output);
        $output = preg_replace('/id="(.*?)"/s', 'id="$1_' . rand() . '"', $output);
        
    } else {
        // sometimes there is non-zero return but there is some output, which is more helpful
        if ($output == "") {
            $output = stream_get_contents($pipes[2]);
        }
    }
    // TODO: maybe return stderr and stdout
    return $output;
}

echo "<hr />";

if (isset($_POST["query"])) {
    $query = $_POST["query"];
    
    // run dot on the input
    echo graphviz($query);
    
}
echo "<hr />";
// TODO some output on the processing time and date
?>
</body>
