<?php
// ------------------------------
// Helper functions
// ------------------------------
function clean($key, $type = 'string', $src = INPUT_POST)
{
    // Pick the right filter (and flags) based on $type
    $filter = FILTER_SANITIZE_SPECIAL_CHARS;
    $options = 0;

    if ($type === 'email') 
	{
        $filter = FILTER_SANITIZE_EMAIL;
    } 
	elseif ($type === 'url') 
	{
        $filter = FILTER_SANITIZE_URL;
    } 
	elseif ($type === 'int') 
	{
        $filter = FILTER_SANITIZE_NUMBER_INT;
    } 
	elseif ($type === 'float') 
	{
        $filter = FILTER_SANITIZE_NUMBER_FLOAT; //strip all except digit and sign
        $options = FILTER_FLAG_ALLOW_FRACTION; //keep the decimal point 
    }

    // Always sanitize first
    $value = filter_input($src, $key, $filter, $options);

    // Always coalesce to '' (if null) and ALWAYS trim the result
    // Read as: "Use $value if set, otherwise ''. Then trim off leading/trailing whitespace."
    return trim($value ?? '');
}

function esc($val)
{
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
?>
