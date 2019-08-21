<?php


namespace Tests\Extension;

use PHPUnit\Runner\BeforeTestHook;

final class TestRunnerExtension implements BeforeTestHook
{
    /**
     * @var string
     */
    private $resultFilePath = 'tests/log/logfile.xml';

    /**
     * @var string
     */
    private $testedFileList = 'tests/log/testedFileList.txt';

    /**
     * @var string
     */
    private $resultDir = 'tests/log/result/';

    /**
     * @var array
     */
    private $classList = [];

    /**
     * PHPUnitのログが出力後にファイル生成を行いたいので
     * __destructで終了処理を記述する。
     */
    public function __destruct()
    {
        if (!file_exists($this->resultFilePath)) {
            return;
        }
        if (count($this->classList) > 1) {
            $this->multiClassTestReport();
        } else {
            $this->singleClassTestReport();
        }
    }

    /**
     * 複数のクラスにまたがって試験した場合は個別のテストクラスも実行する。
     * @throws \ReflectionException
     */
    private function multiClassTestReport()
    {
        exec('xsltproc phpunit.xslt ' . $this->resultFilePath . ' > ' . $this->resultDir . 'output.html');

        if (file_exists($this->testedFileList)) {
            unlink($this->testedFileList);
        }

        foreach ($this->classList as $className) {
            $reflection = new \ReflectionClass($className);
            exec('vendor/bin/phpunit ' . $reflection->getFileName());
        }
    }

    /**
     * 単一のクラスの試験ではクラス名と対応したパスにファイルを生成する。
     */
    private function singleClassTestReport()
    {
        $filePathArr = explode('\\', $this->classList[0]);
        $fileName = array_pop($filePathArr);
        $filePath = implode('/', $filePathArr);

        if (!file_exists($this->resultDir . $filePath)) {
            mkdir($this->resultDir . $filePath, 0777, true);
        }
        exec('xsltproc phpunit.xslt ' . $this->resultFilePath . ' > ' . $this->resultDir . $filePath . '/' . $fileName . '.html');
    }

    /**
     * 実行されたテスト情報からテスト対象のクラス名を取得する。
     * @param string $test
     */
    final public function executeBeforeTest(string $test): void
    {
        $test = substr($test, 0, strpos($test, '::'));
        if (!in_array($test, $this->classList, true)) {
            $this->classList[] = $test;
        }
    }
}
