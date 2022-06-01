<?php

namespace LightSaml\Response;

class PostResponse
{
    protected $statusCode = 200;

    protected $destination;

    protected $params = [];

    public function __construct($destination, array $params)
    {
        $this->destination = $destination;
        $this->params = $params;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getContent()
    {
        $content = <<<'EOT'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>POST data</title>
</head>
<body onload="document.getElementById('a-very-unique-input-id#lightSAML').click();">

    <noscript>
        <p><strong>Note:</strong> Since your browser does not support JavaScript, you must press the button below once to proceed.</p>
    </noscript>

    <form method="post" action="%s">
        <input id="a-very-unique-input-id#lightSAML" type="submit" style="display:none;"/>

        %s

        <noscript>
            <input type="submit" value="Submit" />
        </noscript>

    </form>
</body>
</html>
EOT;
        $fields = '';
        foreach ($this->params as $name => $value) {
            $fields .= sprintf(
                '<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars($name),
                htmlspecialchars($value)
            );
        }

        $content = sprintf($content, htmlspecialchars($this->destination), $fields);

        return $content;
    }
}
