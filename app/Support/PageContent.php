<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Turns the admin-written "Page content" box into HTML.
 *
 *  - If the text already contains HTML tags, it is output exactly as written.
 *  - Otherwise it is treated as plain text, one paragraph per line:
 *      * a short line that does not end with . , ; : or !  -> heading
 *      * lines starting with * - or • -> bullet list; "Label: text" gets a bold label
 *      * [text](https://...) -> link, **bold** and *italic* also work
 */
class PageContent
{
    public static function render(?string $content): string
    {
        $content = trim((string) $content);

        if ($content === '') {
            return '';
        }

        // Written by an admin, so real HTML is passed through untouched.
        if (preg_match('/<\s*(p|h[1-6]|ul|ol|li|div|section|table|a|strong|em|br)[\s>\/]/i', $content)) {
            return $content;
        }

        // Plain-text mode: raw HTML is stripped and unsafe link schemes are blocked.
        return (string) Str::markdown(self::toMarkdown($content), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public static function toMarkdown(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $blocks = [];
        $list = [];
        $flushList = function () use (&$blocks, &$list) {
            if ($list) {
                $blocks[] = implode("\n", $list);
                $list = [];
            }
        };

        // Line by line: text pasted from a web page has one paragraph per line,
        // often with no blank lines between a heading and the text after it.
        foreach (explode("\n", $text) as $raw) {
            $line = rtrim($raw);

            if (trim($line) === '') {
                continue; // a blank line never splits a bullet list
            }

            if (preg_match('/^\s*[-*•]\s+\S/u', $line)) {
                $list[] = self::normaliseBullet($line);
                continue;
            }

            // An indented line right after a bullet continues that bullet.
            if ($list && preg_match('/^\s+\S/', $raw)) {
                $list[count($list) - 1] .= ' '.trim($line);
                continue;
            }

            $flushList();
            $blocks[] = self::looksLikeHeading($line) ? '## '.trim($line) : trim($line);
        }

        $flushList();

        return implode("\n\n", $blocks);
    }

    protected static function normaliseBullet(string $line): string
    {
        // "* Label: text"  ->  "* **Label:** text"
        if (preg_match('/^\s*[-*•]\s+([^:*\[\]]{2,70}):\s+(\S.*)$/u', $line, $m)) {
            return '* **'.trim($m[1]).':** '.$m[2];
        }

        // "• text" -> "* text"
        if (preg_match('/^\s*•\s+(.*)$/u', $line, $m)) {
            return '* '.$m[1];
        }

        return $line;
    }

    protected static function looksLikeHeading(string $line): bool
    {
        $line = trim($line);
        $length = mb_strlen($line);

        if ($length < 3 || $length > 120) {
            return false;
        }

        // Already markdown / a list / a link / a quote / HTML: leave it alone.
        if (preg_match('/^(#|\*|-|>|\||\[|!|<|\d+[.)]\s)/u', $line)) {
            return false;
        }

        // Sentences end with punctuation; headings usually don't.
        return ! preg_match('/[.,;:!]$/u', $line);
    }
}
