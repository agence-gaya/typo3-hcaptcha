<?php

declare(strict_types=1);

use GAYA\Typo3Coder\Configuration\ProjectContext;

return static function (DOMDocument $document, ProjectContext $context, string $suite): void {
    $root = $document->documentElement;
    $root->setAttribute('executionOrder', 'depends,defects');
    $root->setAttribute('beStrictAboutOutputDuringTests', 'true');
    $root->setAttribute('beStrictAboutCoverageMetadata', 'true');
    $root->setAttribute('requireCoverageMetadata', $suite === 'unit' ? 'true' : 'false');
    if ($suite === 'functional') {
        $root->setAttribute('displayDetailsOnTestsThatTriggerWarnings', 'true');
        $root->setAttribute('displayDetailsOnTestsThatTriggerDeprecations', 'true');
        return;
    }

    $exclude = $document->createElement('exclude');
    foreach (['Classes/Exception', 'Classes/ViewHelpers'] as $path) {
        $directory = $document->createElement('directory');
        $directory->setAttribute('suffix', '.php');
        $directory->appendChild($document->createTextNode($context->absolute($path)));
        $exclude->appendChild($directory);
    }

    $root->getElementsByTagName('source')->item(0)->appendChild($exclude);
};
