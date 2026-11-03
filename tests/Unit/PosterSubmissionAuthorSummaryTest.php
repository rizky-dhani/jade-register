<?php

use App\Models\PosterSubmission;

function authorSummary(string $authorNames): string
{
    return (new PosterSubmission(['author_names' => $authorNames]))->author_summary;
}

it('keeps a single author unchanged', function () {
    expect(authorSummary('Zakiyaturohmah'))->toBe('Zakiyaturohmah');
});

it('treats a trailing credential segment as another author', function () {
    expect(authorSummary('Difla Ghafirah S.KG, drg.,Sp.KGA'))->toBe('Difla Ghafirah S.KG + 2');
});

it('replaces co-authors with a count', function () {
    expect(authorSummary('Aleina Indira, Irene Patricia, Abigail Marisca Christy, Alifia Dian Pramesti'))
        ->toBe('Aleina Indira + 3');
});

it('counts every comma-separated author beyond the first', function () {
    expect(authorSummary('Difla Ghafirah S.KG, Andra Khaliza S.KG, Ika Anisyah, drg.,Sp.KGA'))
        ->toBe('Difla Ghafirah S.KG + 4');
});

it('normalises whitespace around names', function () {
    expect(authorSummary('  Elin   Hertiana ,  Sarah Mersil  '))->toBe('Elin Hertiana + 1');
});

it('ignores empty entries from trailing commas', function () {
    expect(authorSummary('Sarah Mersil, Elin Hertiana,'))->toBe('Sarah Mersil + 1');
});

it('returns an empty string when no author is recorded', function () {
    expect(authorSummary(''))->toBe('');
});
