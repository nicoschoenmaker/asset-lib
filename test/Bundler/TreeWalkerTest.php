<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types=1);
namespace Hostnet\Component\Resolver\Bundler;

use Hostnet\Component\Resolver\File;
use Hostnet\Component\Resolver\Import\DependencyNodeInterface;
use Hostnet\Component\Resolver\Import\RootFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\Resolver\Bundler\TreeWalker
 */
class TreeWalkerTest extends TestCase
{
    public function testWalk()
    {
        $seen = [];

        $walker = new TreeWalker(function (DependencyNodeInterface $node) use (&$seen) {
            $seen[] = $node;
        });

        $root        = new RootFile(new File('foo'));
        $child       = new RootFile(new File('foo'));
        $grand_child = new RootFile(new File('foo'));

        $root->addChild($child);
        $child->addChild($grand_child);

        $walker->walk($root);

        self::assertSame([$child, $grand_child], $seen);
    }

    public function testWalkEarlyStop()
    {
        $seen = [];

        $walker = new TreeWalker(function (DependencyNodeInterface $node) use (&$seen) {
            $seen[] = $node;

            return false;
        });

        $root        = new RootFile(new File('foo'));
        $child       = new RootFile(new File('foo'));
        $grand_child = new RootFile(new File('foo'));

        $root->addChild($child);
        $child->addChild($grand_child);

        $walker->walk($root);

        self::assertSame([$child], $seen);
    }
}
