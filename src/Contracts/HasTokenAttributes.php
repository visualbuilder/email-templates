<?php

namespace Visualbuilder\EmailTemplates\Contracts;

/**
 * Implement on a model (the interface is optional - a tokenAttributes()
 * method alone is enough) to expose accessor-backed attributes in the
 * TokenRegistry catalogue that attribute derivation cannot discover,
 * e.g. a first_name accessor that delegates to a related contact model.
 *
 * Returned names are merged with the derived fillable + appended
 * attributes; the hidden list and the token_excluded_attributes secrets
 * denylist still apply.
 */
interface HasTokenAttributes
{
    /**
     * @return array<int, string>
     */
    public function tokenAttributes(): array;
}
