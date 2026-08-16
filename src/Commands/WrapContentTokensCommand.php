<?php

namespace Visualbuilder\EmailTemplates\Commands;

use Illuminate\Console\Command;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

/**
 * One-off content migration: wrap bare ##token## occurrences in template
 * content with the non-editable badge span the editor renders as a chip.
 *
 * Idempotent - already-wrapped tokens are left alone, so it is safe to run
 * repeatedly. Tokens inside HTML tag attributes (e.g. href="##config.app.url##")
 * and inside {{button ...}} pseudo-tokens are deliberately skipped: wrapping
 * them would break the markup. Rendering is unaffected either way -
 * DefaultTokenHelper strips the badge wrapper before replacement.
 */
class WrapContentTokensCommand extends Command
{
    protected $signature = 'filament-email-templates:wrap-tokens {--dry-run : Report what would change without saving}';

    protected $description = 'Wrap bare ##token## occurrences in email template content with the editor badge span';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $templatesUpdated = 0;
        $tokensWrapped = 0;

        EmailTemplate::withTrashed()->orderBy('id')->chunkById(100, function ($templates) use ($dryRun, &$templatesUpdated, &$tokensWrapped) {
            foreach ($templates as $template) {
                if ($template->content === null) {
                    continue;
                }

                [$content, $count] = $this->wrapTokens($template->content);

                if ($count === 0) {
                    continue;
                }

                $this->line(sprintf(
                    '%s#%d %s [%s]: %d token(s) wrapped',
                    $dryRun ? '[dry-run] ' : '',
                    $template->id,
                    $template->key,
                    $template->language,
                    $count
                ));

                if (! $dryRun) {
                    $template->content = $content;
                    $template->save();
                }

                $templatesUpdated++;
                $tokensWrapped += $count;
            }
        });

        $this->info(sprintf(
            '%sWrapped %d token(s) across %d template(s).',
            $dryRun ? '[dry-run] ' : '',
            $tokensWrapped,
            $templatesUpdated
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function wrapTokens(string $content): array
    {
        $count = 0;

        // Alternation order matters: existing badge spans first (protects
        // against double-wrapping), then any HTML tag (protects tokens
        // inside attributes), then {{button}} pseudo tokens (wrapped as
        // button chips), then other {{...}} tokens (left alone), and only
        // then bare ##tokens##.
        $result = preg_replace_callback(
            '/<span[^>]*\bvb-token\b[^>]*>.*?<\/span>|<[^>]*>|\{\{button\b.*?\}\}|\{\{.*?\}\}|##[^#]+##/is',
            function (array $match) use (&$count) {
                if (str_starts_with($match[0], '##')) {
                    $count++;

                    return '<span class="vb-token" contenteditable="false">'.$match[0].'</span>';
                }

                if (preg_match('/^\{\{button\b/i', $match[0])) {
                    $count++;

                    return '<span class="vb-token vb-button-token" contenteditable="false">'.$match[0].'</span>';
                }

                return $match[0];
            },
            $content
        );

        return [$result ?? $content, $count];
    }
}
