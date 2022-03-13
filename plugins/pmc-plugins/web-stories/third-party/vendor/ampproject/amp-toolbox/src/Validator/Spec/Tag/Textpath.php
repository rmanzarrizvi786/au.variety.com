<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */
namespace Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;

use Google\Web_Stories_Dependencies\AmpProject\Attribute;
use Google\Web_Stories_Dependencies\AmpProject\Format;
use Google\Web_Stories_Dependencies\AmpProject\Tag as Element;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\AttributeList;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Identifiable;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\SpecRule;
use Google\Web_Stories_Dependencies\AmpProject\Validator\Spec\Tag;
/**
 * Tag class Textpath.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class Textpath extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'TEXTPATH';
    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [SpecRule::TAG_NAME => Element::TEXTPATH, SpecRule::ATTRS => [Attribute::EXTERNALRESOURCESREQUIRED => [], Attribute::METHOD => [], Attribute::SPACING => [], Attribute::STARTOFFSET => []], SpecRule::ATTR_LISTS => [AttributeList\SvgConditionalProcessingAttributes::ID, AttributeList\SvgCoreAttributes::ID, AttributeList\SvgPresentationAttributes::ID, AttributeList\SvgStyleAttr::ID, AttributeList\SvgXlinkAttributes::ID], SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#svg', SpecRule::MANDATORY_ANCESTOR => Element::SVG, SpecRule::HTML_FORMAT => [Format::AMP, Format::AMP4ADS]];
}
