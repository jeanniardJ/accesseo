<?php

declare(strict_types=1);

use Elao\Bundle\SeoTool\Checker\AccessibilityChecker;
use Elao\Bundle\SeoTool\Checker\Headline;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class AccessibilityCheckerTest extends TestCase
{
    public function testCountHeadlines()
    {
        $optimizationChecker = $this->getAccessibilityChecker('headlines-1-6.html');

        $headlines = [
            'h1' => 2,
            'h2' => 3,
            'h3' => 4,
            'h4' => 1,
            'h5' => 1,
            'h6' => 1,
        ];

        static::assertEquals($headlines, $optimizationChecker->countHeadlinesByHn());
    }

    public function testGetHeadlines()
    {
        $optimizationChecker = $this->getAccessibilityChecker('headlines-1-6.html');

        $h1Headline = new Headline(1, 'This is one h1');
        $firstHeadline = new Headline(1, 'This is h1');
        $firstHeadline->addChild(new Headline(2, 'This is h2'));
        $secondHeadlineOfFirst = new Headline(2, 'Another h2');
        $firstHeadline->addChild($secondHeadlineOfFirst);
        $secondHeadlineOfFirst->addChild(new Headline(3, 'And a h3'));
        $secondHeadlineOfFirst->addChild(new Headline(3, 'Another h3'));

        $thirdHeadlineOfFirst = new Headline(2, 'And so another h2');
        $thirdHeadlineOfFirst->addChild(new Headline(3, 'And a h3'));

        $h3Somewhere = new Headline(3, 'Another h3');

        $nextHeadline = new Headline(4, 'And now a H4');
        $nextNextHeadline = new Headline(5, 'And now a H5');
        $nextNextHeadline->addChild(new Headline(6, 'And now a H6'));
        $nextHeadline->addChild($nextNextHeadline);

        $h3Somewhere->addChild($nextHeadline);

        $thirdHeadlineOfFirst->addChild($h3Somewhere);
        $firstHeadline->addChild($thirdHeadlineOfFirst);

        $treeExpected = [
            $h1Headline,
            $firstHeadline,
        ];

        static::assertEquals(\count($treeExpected), \count($optimizationChecker->getHeadlineTree()));
        static::assertEquals($treeExpected, $optimizationChecker->getHeadlineTree());
    }

    public function testSemanticalSections()
    {
        $accessibilityChecker = $this->getAccessibilityChecker('sections.html');

        static::assertEquals(true, $accessibilityChecker->isHeader());
        static::assertEquals(true, $accessibilityChecker->isFooter());
        static::assertEquals(true, $accessibilityChecker->isArticle());
        static::assertEquals(true, $accessibilityChecker->isAside());
        static::assertEquals(true, $accessibilityChecker->isNavInHeader());
        static::assertEquals(true, $accessibilityChecker->isHeaderInArticle());
    }

    public function testNoSemanticalSections()
    {
        $accessibilityChecker = $this->getAccessibilityChecker('no-sections.html');

        static::assertEquals(false, $accessibilityChecker->isHeader());
        static::assertEquals(true, $accessibilityChecker->isFooter());
        static::assertEquals(false, $accessibilityChecker->isArticle());
        static::assertEquals(true, $accessibilityChecker->isAside());
        static::assertEquals(false, $accessibilityChecker->isNavInHeader());
        static::assertEquals(false, $accessibilityChecker->isHeaderInArticle());
    }

    public function testNoExplicitsButtons()
    {
        $accessibility = $this->getAccessibilityChecker('index.html');
        static::assertEquals(2, $accessibility->countNonExplicitButtons());
    }

    public function testIsThereAForm()
    {
        $form = $this->getAccessibilityChecker('form-missing-for.html');
        $alsoForm = $this->getAccessibilityChecker('form-missing-for.html');
        $noForm = $this->getAccessibilityChecker('index.html');

        static::assertEquals(true, $form->isForm());
        static::assertEquals(true, $alsoForm->isForm());
        static::assertEquals(false, $noForm->isForm());
    }

    public function testMissingForInForm()
    {
        $accessibilityChecker = $this->getAccessibilityChecker('form-missing-for.html');
        static::assertEquals(['name', 'email'], $accessibilityChecker->getListMissingForLabelsInForm());
    }

    public function testNoMissingForInForm()
    {
        $accessibilityChecker = $this->getAccessibilityChecker('form-with-for.html');
        static::assertEquals([2 => ''], $accessibilityChecker->getListMissingForLabelsInForm());
    }

    public function testNoForm()
    {
        $accessibilityChecker = $this->getAccessibilityChecker('headlines-1-6.html');
        static::assertEquals([], $accessibilityChecker->getListMissingForLabelsInForm());
    }

    public function testIsForm()
    {
        $accessibilityChecker = $this->getAccessibilityChecker('form-with-for.html');
        static::assertEquals(true, $accessibilityChecker->isForm());
    }

    public function getAccessibilityChecker($filename): AccessibilityChecker
    {
        $html = file_get_contents(sprintf('Tests/AccessibilityChecker/%s', $filename));
        $crawler = new Crawler($html);

        return new AccessibilityChecker($crawler);
    }
}
