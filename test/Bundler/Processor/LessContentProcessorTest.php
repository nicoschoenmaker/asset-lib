<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types=1);
namespace Hostnet\Component\Resolver\Bundler\Processor;

use Hostnet\Component\Resolver\Bundler\ContentItem;
use Hostnet\Component\Resolver\Bundler\ContentState;
use Hostnet\Component\Resolver\Bundler\Runner\RunnerInterface;
use Hostnet\Component\Resolver\Bundler\Runner\RunnerType;
use Hostnet\Component\Resolver\File;
use Hostnet\Component\Resolver\FileSystem\FileReader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\Resolver\Bundler\Processor\LessContentProcessor
 */
class LessContentProcessorTest extends TestCase
{
    private $runner;
    /**
     * @var LessContentProcessor
     */
    private $less_content_processor;

    protected function setUp()
    {
        $this->runner = $this->prophesize(RunnerInterface::class);

        $this->less_content_processor = new LessContentProcessor(
            $this->runner->reveal()
        );
    }

    public function testSupports()
    {
        self::assertTrue($this->less_content_processor->supports(new ContentState('less')));
        self::assertFalse($this->less_content_processor->supports(new ContentState('less', ContentState::PROCESSED)));
        self::assertFalse($this->less_content_processor->supports(new ContentState('less', ContentState::READY)));
        self::assertFalse($this->less_content_processor->supports(new ContentState('css')));
        self::assertFalse($this->less_content_processor->supports(new ContentState('php')));
        self::assertFalse($this->less_content_processor->supports(new ContentState('json')));
    }

    public function testPeek()
    {
        $state = new ContentState('less');
        $this->less_content_processor->peek(__DIR__, $state);

        self::assertSame('css', $state->extension());
        self::assertSame(ContentState::READY, $state->current());
    }

    public function testTranspile()
    {
        $item = new ContentItem(new File(basename(__FILE__)), 'foobar.less', new FileReader(__DIR__));

        $this->runner->execute(RunnerType::LESS, $item)->willReturn('less code');

        $this->less_content_processor->transpile(__DIR__, $item);

        self::assertContains('less code', $item->getContent());
        self::assertSame('foobar.less', $item->module_name);
        self::assertSame(ContentState::READY, $item->getState()->current());
    }
}
