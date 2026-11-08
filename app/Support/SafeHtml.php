<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML cleaner for admin-written content.
 * Keeps normal formatting (headings, lists, links, images, tables) and removes
 * scripts, event handlers (onerror=...), javascript: links, iframes, forms and styles.
 */
class SafeHtml
{
    protected const TAGS = [
        'p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u', 'small', 'sup', 'sub',
        'blockquote', 'code', 'pre', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'div', 'span',
    ];

    /** Removed together with everything inside them. */
    protected const DROP = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button',
        'textarea', 'select', 'option', 'link', 'meta', 'base', 'svg', 'math',
        'noscript', 'template', 'frame', 'frameset', 'applet', 'audio', 'video',
    ];

    protected const ATTRS = [
        '*'  => ['class'],
        'a'  => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan', 'scope'],
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementsByTagName('div')->item(0);
        if (! $root) {
            return e(strip_tags($html));
        }

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    protected static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);

                if (in_array($tag, self::DROP, true)) {
                    $node->removeChild($child);
                    continue;
                }

                self::walk($child);

                if (! in_array($tag, self::TAGS, true)) {
                    // unknown tag: keep its text/children, drop the tag itself
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }

                self::cleanAttributes($child, $tag);
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
            }
        }
    }

    protected static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $allowed = array_merge(self::ATTRS['*'], self::ATTRS[$tag] ?? []);

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);

            if (! in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            $value = trim(preg_replace('/[\x00-\x20]+/', '', $attr->nodeValue));

            if ($name === 'href' && ! preg_match('#^(https?:|mailto:|tel:|/(?!/)|\#)#i', $value)) {
                $el->removeAttribute('href');
            }
            if ($name === 'src' && ! preg_match('#^(https?:|/(?!/))#i', $value)) {
                $el->removeAttribute('src');
            }
        }

        if ($tag === 'a' && strtolower((string) $el->getAttribute('target')) === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }
}
