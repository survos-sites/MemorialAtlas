# MemorialAtlas

*From gravestone photographs to a searchable record of remembrance.*

MemorialAtlas is a cemetery-focused project for the [Symfony AI & MCP Hackathon](https://symfony.com/hackathon/), built with Symfony, Doctrine and PostgreSQL.

[Live site: MemorialAtlas.org](https://memorialatlas.org) · [Public API documentation](https://memorialatlas.org/api/docs)

Production runs as the Dokku app `memorialatlas` on `fsn1`. The deployment remote is `dokku@fsn1:memorialatlas`.

## Setup

Requires PHP 8.5+ (GD, PDO PostgreSQL and SQLite), Composer, Docker for Imagor,
and PostgreSQL (existing or the optional Compose service below). The [public Sperryville demo](https://huggingface.co/datasets/museado/memorialatlas-demo)
includes four photographs with saved OCR and extraction results.
No S3, Hugging Face or AI credentials are required to load and browse it.

~~~bash
git clone https://github.com/survos-sites/MemorialAtlas.git
cd MemorialAtlas
cp .env.local.example .env.local
composer install
docker compose --profile standalone up -d --wait
bin/load-data
symfony server:start -d          # or: php -S 127.0.0.1:8000 -t public
~~~

Startup creates the application database if needed, applies migrations, downloads
the public demo listing and prints its item URL. Repeating it preserves the existing
demo and review changes. Image URLs and checksums are retained from the manifest. Originals stay on Hugging Face; Imagor fetches them only when a thumbnail is requested.
The selected saved receipts retain source IDs as provenance. No AI calls occur.

The five-minute target assumes PHP/Composer and Docker are already available;
the first container-image download depends on your connection. To use an existing
PostgreSQL server, set DATABASE_URL in .env.local and start only
`docker compose up -d imagor`.
Your database user needs permission to create the application database, or you
can create that database beforehand. Startup never provisions a server.
Use --skip-demo for an empty schema. DEMO_MANIFEST_URL selects a different
public demo manifest.

Search additionally needs Elasticsearch. Configure ELASTICSEARCH_DSN and run
php bin/console elastic:index:rebuild --no-interaction after loading data.
Production deployments rebuild both indexes before releasing the application.
The home, About and record pages do not need Elasticsearch.

S3 and provider credentials are for processing new photographs or the optional
app:demo:publish legacy S3 export, not for loading the public demo.
All /admin routes require ROLE_ADMIN and an administrator password hash.

The committed .env defaults use the shared PostgreSQL service on port 5434;
.env.local.example selects the standalone service on port 5544. Change
`DATABASE_URL` for another developer's server. The optional standalone database uses a fixed port and a Symfony CLI ignore
label so Docker cannot silently replace this explicit connection.

## Image processing

1. Preserve original bytes locally and upload to S3 under `scanfony/originals/{sha256}`.
2. Ask Imagor `/meta` for metadata and RustO OCR. Imagor owns EXIF extraction; the app maps its capture date and GPS into optional image fields. GPS is the camera position, not a surveyed grave location.
3. Ask hosted Jev (`jev-1.14.0`) typed questions about OCR: image type, subject versus context, single versus shared memorial, and text usability.
4. Use text when all routing answers pass the conservative thresholds. Otherwise ask Jev about the image and request a generative visual description. Image questions also cover flowers and associated markers.
5. Generate a structured catalog draft from the evidence and stop at human review.

Jev decisions retain probabilities, confidence, model, timing and token usage. Thresholds are provisional: selected probability at least 0.90 and confidence at least 0.65. Text usability is not proof that OCR matches the photograph. The text route does not assess flowers, accessories, material or condition. Set `SCANFONY_REQUIRE_VISUAL_CONTEXT=1` when those details matter, including a veteran marker next to a headstone. A spouse mentioned in an inscription is not automatically another burial, and proximity alone does not prove accessory ownership.

Image requests use TypeSafe's experimental images API. The app prepares baseline JPEGs within its 280–1568 pixel dimension limits. See [TypeSafe image documentation](https://docs.typesafe.ai/images). The current Symfony TypeSafe bridge does not implement this assets request format, so the app uses Symfony HttpClient with typed questions and response validation.

`SCANFONY_META_OCR=1` requests Imagor OCR. Requested OCR missing from the response is an error. `SCANFONY_OCR_ENABLED=1` enables Mistral fallback when no OCR was obtained. `SCANFONY_VISION_PLATFORM` and `SCANFONY_VISION_MODEL` select the generative provider/model (default Mistral). It must support images and structured output. Hugging Face is not an active inference backend.

Workflow decisions live in the app, with no AI workflow bundle dependency:

```text
Item:  draft → ready → processing → review → complete
                          ↓
                        failed → ready
Image: uploaded → processing → assessed → processed
                       ↓           ↓
                     failed ←──────┘
                       ↓
                    uploaded
```

Processing is synchronous. Completed stages persist their evidence; matching fingerprints reuse AI results on retry. Reviewed records are not automatically regenerated by reimporting them. Audio can be imported with a transcript, but automatic transcription is not connected.

## Cemeteries and your photographs

In **Admin → Cemeteries**, create a cemetery with its actual name and optional locality, then use its numeric ID during import. A cemetery is shared context for many records. Cemetery assignment is optional, can be edited on a record, and is never inferred from camera GPS. Existing records assigned to another cemetery are rejected during import rather than silently moved.

```bash
symfony console app:import /path/to/cemetery-photos --dry-run
symfony console app:import /path/to/cemetery-photos --cemetery=1 --process

# Only use grouping when all selected files describe the same memorial.
symfony console app:import /path/to/one-memorial \
  --one-item='Headstone and veteran marker' --cemetery=1 --process
```

Create a separate cemetery for the Sperryville visit once its name is known. Multiple cemeteries may have the same name; locality distinguishes them. A sign or entrance photograph can receive the `context` role rather than `subject`. Its proposed cemetery name stays evidence for review; it does not automatically rename a cemetery or propagate to unrelated photos.

By default, each image becomes one record. `--images-per-item=N` groups consecutive filenames, not visually detected graves. `--context='Your field notes'` adds user-supplied context. SHA-256 prevents duplicate imports. `--limit` selects before duplicate detection; repeated trials use the same files. `--only=filename` is repeatable. Use `--one-item` for mixed photos/audio.

## TombReader samples

[TombReader](https://www.let.rug.nl/bos/tombreader/) by Johan Bos, University of Groningen, supplies photographs and reference annotations under CC BY-NC 4.0. Preserve attribution and the noncommercial restriction. Reference annotations are evidence for comparison, not guaranteed truth.

Import a directory of JPEGs obtained separately with permission. The app does not require Harvest. Dataset acquisition, reference annotation storage, normalization, and folio publication belong in Harvest and are not part of this demo repository.

```bash
symfony console app:import \
  /path/to/tombreader/images \
  --only=t00000.jpg --only=t00001.jpg --only=t00011.jpg \
  --images-per-item=1 --process
```

For another machine, download and extract the official archive into your own data directory and substitute the extracted `in` directory above. Do not assign these Dutch samples to a local Virginia cemetery. The three samples have been processed through the live pipeline into review drafts. Their current OCR triggered conservative image escalation; a fast text classification alone does not establish OCR accuracy.

## Data model

`Cemetery` holds name and optional locality. `Item` holds an optional cemetery association, field notes, workflow state and draft JSON. `Media` uses Doctrine single-table inheritance for `Image` and `Audio`, with ordered item membership. Image metadata, decisions and extracted people remain JSONB; there are no Person or Family tables. Unknown dates, names and associations stay uncertain for review.

## Validation

```bash
composer validate --no-check-publish
symfony console lint:container
symfony console lint:twig templates
symfony console doctrine:schema:validate
symfony php tests/model.php
symfony php tests/image-decisions.php
```

Tests cover Doctrine hydration, cemetery context, workflow guards, decision routing, cached evidence and provider response failures without paid AI requests.

## Public search, private administration

Open /search without an account. EasyAdmin's sidebar links to the same public search. Editing, deletion and administrative listings require the admin account with ROLE_ADMIN.

Run the command below, then put its hash in .env.local as ADMIN_PASSWORD_HASH='the-generated-hash'. Use single quotes to preserve dollar signs. An empty hash leaves sign-in disabled. No default password is shipped. The login form and admin mutations use CSRF protection.

~~~bash
symfony console security:hash-password
~~~

The media field template uses EA 5's ea() context function. The removed global ea variable must not be used.

## SearchBundle + FieldBundle + Elasticsearch

ImageDocument declares searchable text, sortable fields and facets with Field attributes. ImageSearch uses AbstractFieldSearch for controls and the same field metadata for Elasticsearch mappings. The source record remains in PostgreSQL. Public results contain the projection, not raw provider responses, credentials or local paths.

Names extracted into catalog JSON have a dedicated full-text field, boosted above other text. Inscriptions, descriptions, titles and cemetery names are searchable. Facets include source, donor, cemetery, image type, subject/context, single/shared memorial, flowers, veteran marker, OCR usability and review status. “Not assessed” and “Unassigned” are explicit values. Names mentioned as relatives remain searchable without being asserted as separate burials.

Set ELASTICSEARCH_DSN for Elasticsearch 9. The default uses the existing local service on port 9200; the app's alias is stonereader-images. No PostgreSQL text-search extension is needed.

~~~bash
symfony console elastic:index:rebuild images --keep-old
symfony console elastic:index:status images
# Reconcile image changes and parent record/cemetery changes after imports or admin edits.
symfony console elastic:spool:flush
~~~

The bundle queues image changes by ID in a durable spool. The app also queues affected images when an item or cemetery changes or is deleted. A spool consumer must run regularly for ongoing administration; until it drains, search may show the previous indexed version. Database writes do not depend on Elasticsearch availability. Rebuilds derive the index from current database state.

The projection is a foundation for RAG, not an implemented RAG feature. Embeddings, model/version fingerprints, semantic retrieval and cited generation still need to be added. Do not label lexical relevance scores as model confidence.

## Existing Washington collection

The 58 local photographs in var/datasets/tombstones belong to **Odd Fellows / First Baptist Cemetery, Washington, VA**, as supplied by the photographer. Their source label is **Local cemetery photographs**. All 58 have S3 receipts and completed processing; four took the text-only Jev route. The final church-sign photo is classified as place_reference/context. TombReader has a separate source and donor, with cemetery unassigned.

The earlier SQLite database contains existing S3 upload, OCR and catalog receipts. Preserve it. To migrate those receipts into a newly populated PostgreSQL database:

~~~bash
symfony console app:import var/datasets/tombstones --source='Local cemetery photographs'
symfony console app:restore-legacy-evidence var/scanfony.db
symfony console app:cemetery:create 'Odd Fellows / First Baptist Cemetery' --locality='Washington, VA'
# Use the returned cemetery ID:
symfony console app:import var/datasets/tombstones --cemetery=1 --source='Local cemetery photographs'
symfony console app:images:assess --source='Local cemetery photographs'
symfony console app:import var/datasets/tombstones --source='Local cemetery photographs' --process
symfony console elastic:spool:flush
~~~

Receipt restoration reads SQLite without writing it, matches SHA-256 rather than integer IDs, preserves current results, and retains original provider evidence under legacySqlite. Jev reassessment updates classifications while retaining completed catalog drafts. Previously uploaded S3 originals and OCR are reused.

For new photographs, supply --source and optionally --donor during import. Existing conflicting attribution requires an explicit admin correction.

Additional checks (no paid AI calls):

~~~bash
symfony php tests/admin-preview.php
symfony php tests/search-context.php
symfony php tests/search.php # uses and removes a uniquely named Elasticsearch test index
symfony console doctrine:database:create --env=test --if-not-exists
symfony console doctrine:migrations:migrate --env=test --no-interaction
symfony php tests/access.php # isolated app_test database, test-only password
~~~

## Standalone deployment

MemorialAtlas is deployed independently of Harvest. The repository contains the application, migrations, and tests; photographs, database exports, reference annotations, credentials, and generated folios stay outside Git. Import images from a directory and configure PostgreSQL, Elasticsearch, Imagor/S3, and the chosen AI providers. Harvest can supply images, but is not required at runtime.

The Dokku app is `stonestory` on `fsn1`, served at https://stonestory.survos.com. The PHP buildpack uses `Procfile`, `nginx.conf`, and `app.json`; predeploy compiles assets and applies migrations. Set `DEFAULT_URI` for the public hostname and `SEARCH_INDEX_PREFIX` to isolate the search index. Mount `var/elastic-spool` persistently and run one worker to flush search updates.

Before every deployment, run `symfony console doctrine:schema:validate` against local PostgreSQL. Push the tested commit to the repository, then to Dokku. Load demo data separately after migrations and run `elastic:index:rebuild images --keep-old`. Admin sign-in remains disabled until `ADMIN_PASSWORD_HASH` is configured.

### Local tunnel testing

`survos/cloudflared-bundle` detects this checkout in the Symfony proxy index and reads the running tunnel's ingress. Set `CLOUDFLARED_MACHINE` for your machine, then run `symfony console cloudflared:status`. Run `symfony console cloudflared:ingress scanfony` to preview the additional development route, and `symfony console cloudflared:probe` after that route is active. The bundle guards profiler routes on Cloudflare requests and is disabled in production by default. Development tunnels use `survos.org`; the production hostname remains `stonestory.survos.com`.

### Review consecutive cemetery captures

Keep photographs in capture order. Group clear overview/detail views by matching inscriptions and visible features. A shared surname, nearby GPS or adjacent filenames does not establish the same marker. GPS is the camera position and may drift by ten feet or more. Associated accessories can share a photographic record without becoming another burial. Leave ambiguous matches separate.

Import each reviewed group with `app:import DIRECTORY --one-item="Capture name" --only=overview.jpg --only=detail.jpg --cemetery=ID --process`. Imports reuse exact image hashes and retain completed drafts. A cemetery collection is browsable at `/cemeteries/ID`.

After processing, run `symfony console app:capture:review ID`. This evaluates consecutive photos using Jev's versioned `CaptureQuestions`, OCR and existing observations. It records a review suggestion on the current image; it never moves media or merges items. `--dry-run` lists pending comparisons and `--limit=N` bounds a pilot. Cached decisions are invalidated by changed evidence, protocol or model.

The first Sperryville groupings were proposed through visual review, not generated by Jev. Their provenance is stored separately under `item.aiResults.captureGrouping`. Jev comparisons live under `media.aiResults.captureReview`, with source media IDs, input evidence, question definitions, probabilities, model and usage. These are evidence for future retrieval or agent review, not verified marker identities. The comparisons use text evidence, not direct image pairs; missing visual evidence should remain uncertain. RAG and automatic regrouping are not implemented by this command.

### Replay a reviewed photo batch

The Sperryville batch contains 178 photographs in 150 proposed photographic records.
A reviewed manifest supplies the grouping, not the extracted names or dates. Jev
suggestions are a separate review step and never silently merge records.

The portable dataset directory contains `originals/` and `replay.json`. The
manifest has version `1`, `name`, `context`, `groupingMethod`, ordered `groups`
(each with `files`, `basis`, and optional `sequence`/`status`), and a `sha256` map
keyed by filename. Photographs and this batch's annotations remain outside Git.
Supply the dataset separately to judges alongside the repository.

After configuring the services and provider keys described above, create a
cemetery and use its returned ID (IDs differ between installations):

```bash
symfony console app:cemetery:create Sperryville --locality='Sperryville, VA'
symfony console app:import /path/to/dataset/originals \
  --manifest=/path/to/dataset/replay.json --cemetery=ID \
  --source='Local cemetery photographs' --dry-run
symfony console app:import /path/to/dataset/originals \
  --manifest=/path/to/dataset/replay.json --cemetery=ID \
  --source='Local cemetery photographs' --process
symfony console app:capture:review ID
symfony console elastic:spool:flush
```

Open `/cemeteries/ID` to browse the records. Every listed file is checksum-checked
before import starts. Files outside the manifest are ignored. An existing item
with a different grouping is rejected rather than silently rearranged. On a
fresh database, the normal item/media workflows run Imagor metadata, OCR, Jev
routing, vision when requested by the decision, and structured catalog extraction.
On repeat runs, completed drafts and processed media are reused; failed items
can resume. AI outputs may vary between fresh runs or provider/model versions.
The final `review` state means processing finished and a person should check the
suggested record, not that the facts have been verified.

The Sperryville originals include camera GPS. GPS is retained as capture metadata,
not asserted as an exact grave position. The formal cemetery name is unconfirmed.

Run `symfony php tests/capture-manifest.php` and
`symfony php tests/capture-review.php` for manifest validation and cached decision
checks without paid AI requests.

### Search aggregate items

`/search` searches one document per Item: its suggested title and description,
extracted names and inscriptions, OCR and observations from every attached image,
and available audio transcripts. Results show the photo count and open the full
item. Cemetery, source, record type, review status and photo count are facets.
`/search/images` retains photograph-level search and Jev classification facets.
Both are full-text search; semantic retrieval remains a separate future step.

```bash
symfony console elastic:index:rebuild items --keep-old
symfony console elastic:spool:flush
symfony php tests/item-search.php
symfony php tests/search-context.php
```

Item edits use the bundle's normal indexing listener. Media edits, additions and
removals queue their parent Item; cemetery edits queue affected Items as well as
photographs. Run the spool consumer regularly to keep both indexes current.

### Pilot marker facts

Run `symfony console app:markers:extract ITEM_ID [ITEM_ID ...]` after the normal
photo workflow. This optional synchronous step sends all prepared photographs of
each selected item and supporting OCR to the configured vision platform. Symfony
AI maps the response to `MarkerFacts` and `MarkerPerson` DTOs. Results retain the
model, prompt, input hashes, raw response and supporting media IDs; identical
inputs reuse the previous extraction.

Item search exposes proposed marker orientation/form, family name, named-person
count, recorded-death-date count, birth-only count, other mentioned-person count,
symbols and epitaph themes. Family names retain the extracted spelling, with
capitalization normalized for facets. Unassessed records have unknown counts,
not zero. A missing death date never establishes that someone is living; an
ambiguous lone date should remain unclassified. Date-count facets conservatively exclude people for whom the extraction states any uncertainty; the raw proposed dates remain visible for review. These are proposed inscription facts,
not verified burial records. Details and source references appear on the item.

After first adding these fields, run `elastic:index:rebuild items --keep-old`.
Later extractions are indexed with `elastic:spool:flush`. This pilot is explicit
and is not automatically run for every import.

## OCR-only collections and provider selection

Use OCR-only processing for uniform markers such as Arlington:

```bash
php bin/console app:import /photos/arlington --cemetery=ID --process --text-only
```

The processing policy is saved on each item, including retries. OCR-only mode
uses OCR, Jev text questions, and structured extraction from text. It never sends
images to Jev or the generative vision model, even when a text judgment recommends
escalation. Missing OCR stops for review instead of silently using vision.
Uncertain readings remain proposed; visual details are not assessed. Existing
reviewed records are preserved rather than regenerated.

For varied Sperryville markers, omit `--text-only` to retain conditional image
assessment and visual description. `SCANFONY_VISION_ENABLED=0` makes OCR-only the
default for new records. Structured extraction is independently configured:

```dotenv
SCANFONY_OCR_PLATFORM=mistral
SCANFONY_OCR_MODEL=mistral-ocr-latest
SCANFONY_VISION_PLATFORM=mistral
SCANFONY_VISION_MODEL=mistral-medium-latest
SCANFONY_EXTRACTION_PLATFORM=mistral
SCANFONY_EXTRACTION_MODEL=mistral-medium-latest
```

`mistral` is hosted Mistral. `mistral_rusto` is the separate RustO platform, whether running locally or in production:

```dotenv
MISTRAL_RUSTO_BASE_URL=http://127.0.0.1:8895
MISTRAL_RUSTO_API_KEY=local
MISTRAL_RUSTO_CHAT_MODEL=
```

The current local server supports **OCR only**, with models `tiny` and `small`.
To use it instead of hosted fallback OCR, set `SCANFONY_OCR_PLATFORM=mistral_rusto`
and `SCANFONY_OCR_MODEL=tiny`. Imagor metadata OCR still has priority; set
`SCANFONY_META_OCR=0` to test the selected OCR platform on fresh photographs.
Existing OCR receipts are reused. Hosted OCR remains the default.

When the endpoint gains chat support, set `MISTRAL_RUSTO_CHAT_MODEL` to its actual
model ID, then select `mistral_rusto` and that model independently for vision or
extraction. Vision requires an image-capable model; extraction requires structured
output. The current OCR server cannot do either. The explicit multi-photo
`app:markers:extract` command uses the extraction selector but still requires
vision capabilities; it is not part of the OCR-only import path.

### Mistral OCR bridge fix

The OCR client previously hardcoded `https://api.mistral.ai/v1/ocr`, ignoring the
factory's base URL. The fix is prepared in the Symfony AI source checkout with a
regression covering direct client calls, factory forwarding, trailing slashes,
and the unchanged hosted default. Related upstream configuration issue:
https://github.com/symfony/ai/issues/2900.

Until a release contains it, Composer's install/update hooks apply the guarded,
idempotent `bin/apply-mistral-ocr-fix.php`. Unexpected upstream source changes stop
the patch instead of silently sending local OCR to the hosted service. No fork
credentials or absolute developer paths are required by this patch.


## Public genealogy API

API Platform serves an anonymous, read-only API with interactive documentation at
`/api/docs`. Use `Accept: application/ld+json` for paginated collections with
`member`, `totalItems` and next-page links, or `Accept: application/json` for JSON.

- `GET /api/gravestones`: captured records, 30 per page.
- `GET /api/gravestones?cemetery=2&recordType=gravestone`: scope to a cemetery
  and exclude captures that have not been classified as gravestones.
- `GET /api/gravestones/69`: one record with cemetery, media, OCR, image
  coordinates, descriptions and structured claims.
- `GET /api/docs.jsonopenapi`: the OpenAPI schema.

The collection includes unclassified captures; check `recordType` and `marking`.
`claims.catalog.record` and `claims.markerFacts.record` contain extracted people,
dates, inscriptions and other available facts with model provenance. These are
AI claims requiring review, not resolved person identities. Keep cemetery and
record identifiers when creating a genealogy graph. Photo coordinates describe
the camera position, not a surveyed grave location. Media `contentUrl` values
resolve against this site's origin.

Cross-origin reads are public. Writes are disabled. Internal storage paths,
donor details, errors and raw AI request payloads are excluded.

Run `php tests/public-api.php` against the local server to check the contract.

### Thumbnail and optional database services

Start the stock Imagor image with `docker compose up -d imagor`. Symfony UX Image
uses the UX Imagor bridge through `UX_IMAGE_DSN`; its default points to localhost
port 8088. Public Hugging Face photos need no storage credentials. Cache files
stay under `var/imagor` on the host. This unsigned, localhost-only configuration
is for development; use a signed HTTPS Imagor endpoint for a public deployment.

Keep using your existing PostgreSQL server, or start the optional Timescale
PostgreSQL 18 service with `docker compose --profile standalone up -d`.
For that service, set this in `.env.local` before running `bin/load-data`:

```dotenv
DATABASE_URL="postgresql://app:app@127.0.0.1:5544/memorialatlas?serverVersion=18&charset=utf8"
```

The image includes pgvector, enabled during initial database creation. PostgreSQL
already includes full-text search. The application still uses Elasticsearch for
its search page; adding this service does not change the search backend.
Database files live in `var/postgres`, or the host directory supplied through
`POSTGRES_DATA_DIR`. No database volume or disk-size limit is declared. The
Symfony CLI service-ignore label prevents automatic DATABASE_URL overrides.
