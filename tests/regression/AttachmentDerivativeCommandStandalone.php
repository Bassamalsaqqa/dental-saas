<?php
declare(strict_types=1);
namespace CodeIgniter\CLI {
    class BaseCommand {}
    class CLI {
        public static array $options = [];
        public static array $errors = [];
        public static function getOption($name) { return self::$options[$name] ?? null; }
        public static function error($message) { self::$errors[] = $message; }
    }
}
namespace Config {
    class Database {
        public static int $connections = 0;
        public static function connect() { self::$connections++; throw new \RuntimeException('Unexpected database access'); }
    }
}
namespace {
    define('EXIT_ERROR', 1);
    $configuration = (object) ['enabled' => false];
    function config($name) { return $GLOBALS['configuration']; }
    require __DIR__ . '/../../app/Commands/BuildAttachmentDerivatives.php';
    $command = new App\Commands\BuildAttachmentDerivatives();
    $checks = 0;
    foreach ([[], ['clinic' => '0'], ['clinic' => '-1'], ['clinic' => '1e2'], ['clinic' => ' 1'], ['clinic' => ['1']],
        ['clinic' => '4294967296'], ['clinic' => '18446744073709551617'],
        ['clinic' => '1', 'after-id' => '9223372036854775808'], ['clinic' => '1', 'after-id' => '-1'],
        ['clinic' => '1', 'after-id' => ['0']], ['clinic' => '1', 'limit' => '101'],
        ['clinic' => '1', 'limit' => '0'], ['clinic' => '1', 'limit' => true],
        ['clinic' => '1', 'limit' => '18446744073709551617'], ['clinic' => '1', 'limit' => "25\n"],
        ['clinic' => '1', 'after-id' => '9223372036854775808', 'report' => true]] as $options) {
        CodeIgniter\CLI\CLI::$options = $options;
        CodeIgniter\CLI\CLI::$errors = [];
        if ($command->run([]) !== EXIT_ERROR || count(CodeIgniter\CLI\CLI::$errors) !== 1 || Config\Database::$connections !== 0) {
            throw new RuntimeException('Invalid options reached database: ' . json_encode($options));
        }
        $checks++;
    }
    // A default-off generation request must also stop before a connection is opened.
    CodeIgniter\CLI\CLI::$options = ['clinic' => '0001', 'after-id' => '0000', 'limit' => '025'];
    CodeIgniter\CLI\CLI::$errors = [];
    if ($command->run([]) !== EXIT_ERROR || CodeIgniter\CLI\CLI::$errors !== ['Derivative generation is disabled.'] || Config\Database::$connections !== 0) {
        throw new RuntimeException('Disabled generator accessed a database');
    }
    $checks++;
    $method = new ReflectionMethod($command, 'unsignedOption');
    foreach ([['0001', 1, 100, 1], ['0', 0, PHP_INT_MAX, 0], ['100', 1, 100, 100],
        [(string) PHP_INT_MAX, 0, PHP_INT_MAX, PHP_INT_MAX], ['4294967295', 1, 4294967295, 4294967295],
        [str_repeat('0', 40) . '25', 1, 100, 25]] as [$value, $minimum, $maximum, $expected]) {
        if ($method->invoke($command, $value, $minimum, $maximum) !== $expected) { throw new RuntimeException('Boundary parsing mismatch'); }
        $checks++;
    }
    echo json_encode(['status' => 'PASS', 'checks' => $checks, 'scope' => 'Actual command option validation and default-OFF early exit with database-access trap; no SQL or delivery claim']), PHP_EOL;
}
