<?php

function getEnvBool(string $key, bool $default): bool {
    $val = getenv($key);
    if ($val === false || $val === '') {
        return $default;
    }

    return filter_var($val, FILTER_VALIDATE_BOOLEAN);
}

// 1. Fetch the GitHub event payload file path
$eventPath = getenv('GITHUB_EVENT_PATH');
if (!$eventPath || !file_exists($eventPath)) {
    fwrite(STDERR, "❌ Error: GITHUB_EVENT_PATH file not found.\n");
    exit(1);
}

// 2. Decode the JSON event payload
$payload = json_decode(file_get_contents($eventPath), true);
$pullRequest = $payload['pull_request'] ?? null;

if (!$pullRequest) {
    echo "ℹ️ This event is not a Pull Request. Skipping validation.\n";
    exit(0);
}

// 3. Extract branch name and PR title
$branchName = $pullRequest['head']['ref'] ?? '';
$prTitle = $pullRequest['title'] ?? '';

// 4. Load configuration inputs from environment variables
$validateBranch = getEnvBool('VALIDATE_BRANCH', true);
$validateTitle = getEnvBool('VALIDATE_TITLE', true);
$requireTicket = getEnvBool('REQUIRE_TICKET', true);

$rawPrefixes = getenv('ALLOWED_PREFIXES') ?: 'feature,bugfix,hotfix';
$allowedPrefixes = array_filter(array_map('trim', explode(',', $rawPrefixes)));
$prefixesPattern = implode('|', array_map('preg_quote', $allowedPrefixes));

// 5. Build dynamic PCRE regex rules or use custom overrides
$branchRegex = getenv('BRANCH_REGEX')
    ?: "/^({$prefixesPattern})\/" . ($requireTicket
        ? '[A-Z]+-\d+-'
        : '') . ".+$/";
$titleRegex = getenv('TITLE_REGEX')
    ?: ($requireTicket
        ? '/^\[[A-Z]+-\d+\] .+'
        : '/^.+/');

$errors = [];

// 6. Perform validation checks
if ($validateBranch) {
    if (!preg_match($branchRegex, $branchName)) {
        $errors[] = "❌ Invalid branch name '{$branchName}'. "
            . "Expected format matching '{$branchRegex}'. "
            . "Allowed prefixes: "
            . implode(', ', $allowedPrefixes) . PHP_EOL;
    }
}

if ($validateTitle) {
    if (!preg_match($titleRegex, $prTitle)) {
        $errors[] = "❌ Invalid PR title '{$prTitle}'. Expected format matching '{$titleRegex}'" . "\n";
    }
}

// 7. Output results and exit status
if (!empty($errors)) {
    fwrite(STDERR, implode("", $errors) . PHP_EOL);
    exit(1);
}

echo "✅ PR metadata validation successful for branch '{$branchName}'." . PHP_EOL;
exit(0);
