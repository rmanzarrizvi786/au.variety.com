<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Extension;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\DescendantTagList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class AmpStoryAutoAdsTemplate.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<array<string>> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 * @property-read string $descendantTagList
 */
final class AmpStoryAutoAdsTemplate extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-story-auto-ads > template';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::TEMPLATE, SpecRule::SPEC_NAME => 'amp-story-auto-ads > template', SpecRule::MANDATORY_PARENT => Extension::STORY_AUTO_ADS, SpecRule::ATTRS => [Attribute::TYPE => [SpecRule::MANDATORY => \true, SpecRule::VALUE => ['amp-mustache'], SpecRule::DISPATCH_KEY => 'NAME_VALUE_PARENT_DISPATCH']], SpecRule::REFERENCE_POINTS => [[SpecRule::TAG_SPEC_NAME => 'AMP-STORY-GRID-LAYER default'], [SpecRule::TAG_SPEC_NAME => 'AMP-STORY-GRID-LAYER animate-in']], SpecRule::HTML_FORMAT => [Format::AMP], SpecRule::REQUIRES_EXTENSION => [Extension::MUSTACHE], SpecRule::DESCENDANT_TAG_LIST => DescendantTagList\AmpStoryGridLayerAllowedDescendants::ID];
}
