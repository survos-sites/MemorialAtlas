<?php

// This file is auto-generated and is for apps only. Bundles SHOULD NOT rely on its content.

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Config\Loader\ParamConfigurator as Param;

/**
 * This class provides array-shapes for configuring the services and bundles of an application.
 *
 * Services declared with the config() method below are autowired and autoconfigured by default.
 *
 * This is for apps only. Bundles SHOULD NOT use it.
 *
 * Example:
 *
 *     ```php
 *     // config/services.php
 *     namespace Symfony\Component\DependencyInjection\Loader\Configurator;
 *
 *     return App::config([
 *         'services' => [
 *             'App\\' => [
 *                 'resource' => '../src/',
 *             ],
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type ImportsConfig = list<string|array{
 *     resource: string,
 *     type?: string|null,
 *     ignore_errors?: bool|'not_found',
 * }>
 * @psalm-type ParametersConfig = array<string, scalar|\UnitEnum|array<scalar|\UnitEnum|array<mixed>|Param|null>|Param|null>
 * @psalm-type ArgumentsType = list<mixed>|array<string, mixed>
 * @psalm-type CallType = array<string, ArgumentsType>|array{0:string, 1?:ArgumentsType, 2?:bool}|array{method:string, arguments?:ArgumentsType, returns_clone?:bool}
 * @psalm-type TagsType = list<string|array<string, array<string, mixed>>> // arrays inside the list must have only one element, with the tag name as the key
 * @psalm-type CallbackType = string|array{0:string|ReferenceConfigurator,1:string}|\Closure|ReferenceConfigurator
 * @psalm-type DeprecationType = array{package: string, version: string, message?: string}
 * @psalm-type DefaultsType = array{
 *     public?: bool,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 * }
 * @psalm-type InstanceofType = array{
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     factory?: CallbackType,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type DefinitionType = array{
 *     class?: string,
 *     file?: string,
 *     parent?: string,
 *     shared?: bool,
 *     synthetic?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     configurator?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     decorates?: string,
 *     decorates_tag?: string,
 *     decoration_within?: string|list<string>,
 *     decoration_around?: string|list<string>,
 *     decoration_inner_name?: string,
 *     decoration_priority?: int,
 *     decoration_on_invalid?: 'exception'|'ignore'|null,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 *     from_callable?: CallbackType,
 * }
 * @psalm-type AliasType = string|array{
 *     alias: string,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 * }
 * @psalm-type PrototypeType = array{
 *     resource: string,
 *     namespace?: string,
 *     exclude?: string|list<string>,
 *     parent?: string,
 *     shared?: bool,
 *     lazy?: bool|string,
 *     public?: bool,
 *     abstract?: bool,
 *     deprecated?: DeprecationType,
 *     factory?: CallbackType,
 *     arguments?: ArgumentsType,
 *     properties?: array<string, mixed>,
 *     configurator?: CallbackType,
 *     calls?: list<CallType>,
 *     tags?: TagsType,
 *     resource_tags?: TagsType,
 *     autowire?: bool,
 *     autoconfigure?: bool,
 *     bind?: array<string, mixed>,
 *     constructor?: string,
 * }
 * @psalm-type StackType = array{
 *     stack: list<DefinitionType|AliasType|PrototypeType|array<class-string, ArgumentsType|null>>,
 *     public?: bool,
 *     deprecated?: DeprecationType,
 *     decorates?: string,
 *     decorates_tag?: string,
 *     decoration_within?: string|list<string>,
 *     decoration_around?: string|list<string>,
 *     decoration_inner_name?: string,
 *     decoration_priority?: int,
 *     decoration_on_invalid?: 'exception'|'ignore'|null,
 * }
 * @psalm-type ServicesConfig = array{
 *     _defaults?: DefaultsType,
 *     _instanceof?: array<class-string, InstanceofType>,
 *     ...<string, DefinitionType|AliasType|PrototypeType|StackType|ArgumentsType|null>
 * }
 * @psalm-type ExtensionType = array<mixed>|scalar|\UnitEnum|Param|null
 * @psalm-type RouterConfig = bool|array{
 *     enabled?: bool|Param, // Default: false
 *     resource?: scalar|Param|null, // Default: null
 *     type?: scalar|Param|null,
 *     default_uri?: scalar|Param|null, // The default URI used to generate URLs in a non-HTTP context. // Default: null
 *     http_port?: scalar|Param|null, // Default: 80
 *     https_port?: scalar|Param|null, // Default: 443
 *     strict_requirements?: scalar|Param|null, // Set to true to throw an exception when a parameter does not match the requirements. Set to false to disable exceptions when a parameter does not match the requirements (and return null instead). Set to null to disable parameter checks against requirements. Defaults to true in debug mode and to null otherwise.
 *     utf8?: bool|Param, // Default: true
 * }
 * @psalm-type CacheConfig = array{
 *     prefix_seed?: scalar|Param|null, // Used to namespace cache keys when using several apps with the same shared backend. // Default: "_%kernel.project_dir%.%kernel.container_class%"
 *     app?: scalar|Param|null, // App related cache pools configuration. Cannot be combined with "default_provider". // Default: "cache.adapter.filesystem"
 *     system?: scalar|Param|null, // System related cache pools configuration. // Default: "cache.adapter.system"
 *     directory?: scalar|Param|null, // Default: "%kernel.share_dir%/pools/app"
 *     default_provider?: scalar|Param|null, // DSN of the backend to use for "cache.app"; the adapter is deduced from it. Replaces "app", which cannot be set alongside it.
 *     default_psr6_provider?: scalar|Param|null,
 *     default_redis_provider?: scalar|Param|null, // Default: "redis://localhost"
 *     default_valkey_provider?: scalar|Param|null, // Default: "valkey://localhost"
 *     default_memcached_provider?: scalar|Param|null, // Default: "memcached://localhost"
 *     default_doctrine_dbal_provider?: scalar|Param|null, // Default: "database_connection"
 *     default_pdo_provider?: scalar|Param|null, // Default: null
 *     default_mongodb_provider?: scalar|Param|null, // Default: "mongodb://localhost/app"
 *     pools?: array<string, array{ // Default: []
 *         adapters?: Param|string|list<scalar|Param|null>,
 *         tags?: scalar|Param|null, // Default: null
 *         public?: bool|Param, // Default: false
 *         default_lifetime?: scalar|Param|null, // Default lifetime of the pool.
 *         provider?: scalar|Param|null, // Overwrite the setting from the default provider for this adapter.
 *         early_expiration_message_bus?: scalar|Param|null,
 *         clearer?: scalar|Param|null,
 *         marshaller?: scalar|Param|null, // The marshaller service to use for this pool.
 *     }>,
 * }
 * @psalm-type AssetConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     strict_mode?: bool|Param, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *     version_strategy?: scalar|Param|null, // Default: null
 *     version?: scalar|Param|null, // Default: null
 *     version_format?: scalar|Param|null, // Default: "%%s?%%s"
 *     json_manifest_path?: scalar|Param|null, // Default: null
 *     base_path?: scalar|Param|null, // Default: ""
 *     base_urls?: Param|string|list<scalar|Param|null>,
 *     packages?: array<string, array{ // Default: []
 *         strict_mode?: bool|Param, // Throw an exception if an entry is missing from the manifest.json. // Default: false
 *         version_strategy?: scalar|Param|null, // Default: null
 *         version?: scalar|Param|null,
 *         version_format?: scalar|Param|null, // Default: null
 *         json_manifest_path?: scalar|Param|null, // Default: null
 *         base_path?: scalar|Param|null, // Default: ""
 *         base_urls?: Param|string|list<scalar|Param|null>,
 *     }>,
 * }
 * @psalm-type SerializerConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     enable_attributes?: bool|Param, // Default: true
 *     name_converter?: scalar|Param|null,
 *     circular_reference_handler?: scalar|Param|null,
 *     max_depth_handler?: scalar|Param|null,
 *     mapping?: array{
 *         paths?: list<scalar|Param|null>,
 *     },
 *     default_context?: array<string, mixed>,
 *     named_serializers?: array<string, array{ // Default: []
 *         name_converter?: scalar|Param|null,
 *         default_context?: array<string, mixed>,
 *         include_built_in_normalizers?: bool|Param, // Whether to include the built-in normalizers // Default: true
 *         include_built_in_encoders?: bool|Param, // Whether to include the built-in encoders // Default: true
 *     }>,
 * }
 * @psalm-type ValidationConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     enable_attributes?: bool|Param, // Default: true
 *     static_method?: Param|string|list<scalar|Param|null>,
 *     translation_domain?: scalar|Param|null, // Default: "validators"
 *     email_validation_mode?: "html5"|"html5-allow-no-tld"|"strict"|Param, // Default: "html5"
 *     mapping?: array{
 *         paths?: list<scalar|Param|null>,
 *     },
 *     not_compromised_password?: bool|array{
 *         enabled?: bool|Param|null, // When disabled, compromised passwords will be accepted as valid. Defaults to false when "framework.test" is enabled, to true otherwise. // Default: null
 *         endpoint?: scalar|Param|null, // API endpoint for the NotCompromisedPassword Validator. // Default: null
 *     },
 *     disable_translation?: bool|Param, // Default: false
 *     property_metadata_existence_check?: bool|Param, // When enabled, validateProperty() and validatePropertyValue() throw an exception if no metadata is found for the given property. // Default: false
 *     auto_mapping?: array<string, array{ // Default: []
 *         services?: list<scalar|Param|null>,
 *     }>,
 * }
 * @psalm-type TranslationConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     fallbacks?: Param|string|list<scalar|Param|null>,
 *     logging?: bool|Param, // Default: false
 *     formatter?: scalar|Param|null, // Default: "translator.formatter.default"
 *     cache_dir?: scalar|Param|null, // Default: "%kernel.cache_dir%/translations"
 *     default_path?: scalar|Param|null, // The default path used to load translations. // Default: "%kernel.project_dir%/translations"
 *     paths?: list<scalar|Param|null>,
 *     pseudo_localization?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *         accents?: bool|Param, // Default: true
 *         expansion_factor?: float|Param, // Default: 1.0
 *         brackets?: bool|Param, // Default: true
 *         parse_html?: bool|Param, // Default: false
 *         localizable_html_attributes?: list<scalar|Param|null>,
 *     },
 *     providers?: array<string, array{ // Default: []
 *         dsn?: scalar|Param|null,
 *         domains?: array<string, scalar|Param|null>,
 *         locales?: list<scalar|Param|null>,
 *     }>,
 *     globals?: array<string, Param|string|array{ // Default: []
 *         value?: mixed,
 *         message?: string|Param,
 *         parameters?: array<string, scalar|Param|null>,
 *         domain?: string|Param,
 *     }>,
 * }
 * @psalm-type LockConfig = Param|bool|string|array{
 *     enabled?: bool|Param, // Default: true
 *     resources?: Param|string|array<string, Param|string|list<mixed>>,
 * }
 * @psalm-type WorkflowConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     workflows?: array<string, array{ // Default: []
 *         audit_trail?: bool|array{
 *             enabled?: bool|Param, // Default: false
 *         },
 *         type?: "workflow"|"state_machine"|Param, // Default: "state_machine"
 *         marking_store?: array{
 *             type?: "method"|Param,
 *             property?: scalar|Param|null,
 *             service?: scalar|Param|null,
 *         },
 *         supports?: Param|string|list<scalar|Param|null>,
 *         definition_validators?: list<scalar|Param|null>,
 *         support_strategy?: scalar|Param|null,
 *         initial_marking?: \BackedEnum|Param|string|list<scalar|Param|null>,
 *         events_to_dispatch?: null|list<string|Param>,
 *         places?: Param|string|list<Param|string|array{ // Default: []
 *             name?: scalar|Param|null,
 *             metadata?: array<string, mixed>,
 *         }>,
 *         transitions?: list<array{ // Default: []
 *             name?: string|Param,
 *             guard?: string|Param, // An expression to block the transition.
 *             from?: \BackedEnum|Param|string|list<Param|string|array{ // Default: []
 *                 place?: string|Param,
 *                 weight?: int|Param, // Default: 1
 *             }>,
 *             to?: \BackedEnum|Param|string|list<Param|string|array{ // Default: []
 *                 place?: string|Param,
 *                 weight?: int|Param, // Default: 1
 *             }>,
 *             weight?: int|Param, // Default: 1
 *             metadata?: array<string, mixed>,
 *         }>,
 *         metadata?: array<string, mixed>,
 *     }>,
 * }
 * @psalm-type TypeInfoConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     aliases?: array<string, scalar|Param|null>,
 * }
 * @psalm-type PropertyAccessConfig = bool|array{ // Property access configuration
 *     enabled?: bool|Param, // Default: true
 *     magic_call?: bool|Param, // Default: false
 *     magic_get?: bool|Param, // Default: true
 *     magic_set?: bool|Param, // Default: true
 *     throw_exception_on_invalid_index?: bool|Param, // Default: false
 *     throw_exception_on_invalid_property_path?: bool|Param, // Default: true
 *     wildcard_reads?: bool|Param, // Enables reading every element of a collection through a "[*]" wildcard. // Default: false
 * }
 * @psalm-type PropertyInfoConfig = bool|array{ // Property info configuration
 *     enabled?: bool|Param, // Default: true
 *     with_constructor_extractor?: bool|Param, // Registers the constructor extractor. // Default: true
 * }
 * @psalm-type UidConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     default_uuid_version?: 7|6|4|1|Param, // Default: 7
 *     name_based_uuid_version?: 5|3|Param, // Default: 5
 *     name_based_uuid_namespace?: scalar|Param|null,
 *     time_based_uuid_version?: 7|6|1|Param, // Default: 7
 *     time_based_uuid_node?: scalar|Param|null,
 *     uuid47_secret?: scalar|Param|null, // A high-entropy secret used by the "uuid47_transformer" service. Defaults to the "kernel.secret" parameter; the service is not registered when neither is defined. // Default: null
 * }
 * @psalm-type AssetMapperConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     paths?: Param|string|array<string, scalar|Param|null>,
 *     excluded_patterns?: list<scalar|Param|null>,
 *     exclude_dotfiles?: bool|Param, // If true, any files starting with "." will be excluded from the asset mapper. // Default: true
 *     server?: bool|Param, // If true, a "dev server" will return the assets from the public directory (true in "debug" mode only by default). // Default: "%kernel.debug%"
 *     public_prefix?: scalar|Param|null, // The public path where the assets will be written to (and served from when "server" is true). // Default: "/assets/"
 *     metadata_dir?: scalar|Param|null, // The directory where "asset-map:compile" writes manifest.json, importmap.json and entrypoint.*.json. These files are read by PHP only, so they do not need to be exposed to the browser. // Default: null
 *     missing_import_mode?: "strict"|"warn"|"ignore"|Param|null, // Behavior if an asset cannot be found when imported from JavaScript or CSS files - e.g. "import './non-existent.js'". "strict" means an exception is thrown, "warn" means a warning is logged, "ignore" means the import is left as-is. Defaults to "strict" in debug mode and to "warn" otherwise. // Default: null
 *     extensions?: array<string, scalar|Param|null>,
 *     importmap_path?: scalar|Param|null, // The path of the importmap.php file. // Default: "%kernel.project_dir%/importmap.php"
 *     importmap_polyfill?: scalar|Param|null, // The importmap name that will be used to load the polyfill. Set to false to disable. // Default: "es-module-shims"
 *     importmap_entries?: "all"|"reachable"|Param, // Which entries end up in the rendered importmap: "all" of them, or only the ones "reachable" from the rendered entrypoints (their eager and lazy import chains) plus the polyfill. // Default: "all"
 *     importmap_script_attributes?: array<string, scalar|Param|null>,
 *     importmap_integrity_algorithms?: list<"sha256"|"sha384"|"sha512"|Param>,
 *     vendor_dir?: scalar|Param|null, // The directory to store JavaScript vendors. // Default: "%kernel.project_dir%/assets/vendor"
 *     minimum_release_age?: int|Param, // Minimum age in seconds a package version must have to be considered when checking for updates (0 disables the check). Enabling it makes update checks download the full npm metadata document, which is larger than the abbreviated one. // Default: 0
 *     precompress?: bool|array{ // Precompress assets with Brotli, Zstandard and gzip.
 *         enabled?: bool|Param, // Default: false
 *         formats?: list<scalar|Param|null>,
 *         extensions?: list<scalar|Param|null>,
 *     },
 * }
 * @psalm-type HttpClientConfig = bool|array{
 *     enabled?: bool|Param, // Default: true
 *     max_host_connections?: int|Param, // The maximum number of connections to a single host.
 *     recorder?: bool|array{ // Record HTTP exchanges into HAR files and replay them in tests (see RecorderHttpClient).
 *         enabled?: bool|Param|null, // Defaults to true when "framework.test" is enabled, to false otherwise. // Default: null
 *         redactor?: scalar|Param|null, // Service id of a RedactorInterface; when set, "redact" and "redact_except" are ignored. // Default: null
 *         redact?: list<scalar|Param|null>,
 *         redact_except?: list<scalar|Param|null>,
 *     },
 *     default_options?: array{
 *         vars?: array<string, mixed>,
 *         headers?: array<string, mixed>,
 *         max_redirects?: int|Param, // The maximum number of redirects to follow.
 *         http_version?: scalar|Param|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *         resolve?: array<string, scalar|Param|null>,
 *         proxy?: scalar|Param|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *         no_proxy?: scalar|Param|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *         timeout?: float|Param, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *         max_duration?: float|Param, // The maximum execution time for the request+response as a whole.
 *         max_connect_duration?: float|Param, // The maximum duration allowed for DNS + TCP + TLS connection; a value lower than or equal to 0 means unlimited.
 *         bindto?: scalar|Param|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *         verify_peer?: bool|Param, // Indicates if the peer should be verified in a TLS context.
 *         verify_host?: bool|Param, // Indicates if the host should exist as a certificate common name.
 *         cafile?: scalar|Param|null, // A certificate authority file.
 *         capath?: scalar|Param|null, // A directory that contains multiple certificate authority files.
 *         local_cert?: scalar|Param|null, // A PEM formatted certificate file.
 *         local_pk?: scalar|Param|null, // A private key file.
 *         passphrase?: scalar|Param|null, // The passphrase used to encrypt the "local_pk" file.
 *         ciphers?: scalar|Param|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...).
 *         peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *             sha1?: mixed,
 *             pin-sha256?: mixed,
 *             md5?: mixed,
 *         },
 *         crypto_method?: scalar|Param|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *         extra?: array<string, mixed>,
 *         rate_limiter?: scalar|Param|null, // Rate limiter name to use for throttling requests. // Default: null
 *         caching?: bool|array{ // Caching configuration.
 *             enabled?: bool|Param, // Default: false
 *             cache_pool?: string|Param, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *             shared?: bool|Param, // Indicates whether the cache is shared (public) or private. // Default: true
 *             max_ttl?: int|Param, // The maximum TTL (in seconds) allowed for cached responses. // Default: 86400
 *         },
 *         retry_failed?: bool|array{
 *             enabled?: bool|Param, // Default: false
 *             base_uris?: Param|string|list<string|Param>,
 *             retry_strategy?: scalar|Param|null, // service id to override the retry strategy. // Default: null
 *             http_codes?: Param|int|string|array<string, array{ // Default: []
 *                 code?: int|Param,
 *                 methods?: Param|string|list<string|Param>,
 *             }>,
 *             max_retries?: int|Param, // Default: 3
 *             delay?: int|Param, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *             multiplier?: float|Param, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *             max_delay?: int|Param, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *             jitter?: float|Param, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *         },
 *     },
 *     mock_response_factory?: scalar|Param|null, // `true` to always return empty 200 responses, or the id of the service to use to generate mock responses - which should be either an invokable or an iterable.
 *     scoped_clients?: array<string, Param|string|array{ // Default: []
 *         scope?: scalar|Param|null, // The regular expression that the request URL must match before adding the other options. When none is provided, the base URI is used instead.
 *         base_uri?: scalar|Param|null, // The URI to resolve relative URLs, following rules in RFC 3985, section 2.
 *         auth_basic?: scalar|Param|null, // An HTTP Basic authentication "username:password".
 *         auth_bearer?: scalar|Param|null, // A token enabling HTTP Bearer authorization.
 *         auth_ntlm?: scalar|Param|null, // A "username:password" pair to use Microsoft NTLM authentication (requires the cURL extension).
 *         query?: array<string, scalar|Param|null>,
 *         mock_response_factory?: scalar|Param|null, // `true` to always return empty 200 responses, `false` to disable mocking, or the id of the service to use to generate mock responses (invokable or iterable).
 *         headers?: array<string, mixed>,
 *         max_redirects?: int|Param, // The maximum number of redirects to follow.
 *         http_version?: scalar|Param|null, // The default HTTP version, typically 1.1 or 2.0, leave to null for the best version.
 *         resolve?: array<string, scalar|Param|null>,
 *         proxy?: scalar|Param|null, // The URL of the proxy to pass requests through or null for automatic detection.
 *         no_proxy?: scalar|Param|null, // A comma separated list of hosts that do not require a proxy to be reached.
 *         timeout?: float|Param, // The idle timeout, defaults to the "default_socket_timeout" ini parameter.
 *         max_duration?: float|Param, // The maximum execution time for the request+response as a whole.
 *         max_connect_duration?: float|Param, // The maximum duration allowed for DNS + TCP + TLS connection; a value lower than or equal to 0 means unlimited.
 *         bindto?: scalar|Param|null, // A network interface name, IP address, a host name or a UNIX socket to bind to.
 *         verify_peer?: bool|Param, // Indicates if the peer should be verified in a TLS context.
 *         verify_host?: bool|Param, // Indicates if the host should exist as a certificate common name.
 *         cafile?: scalar|Param|null, // A certificate authority file.
 *         capath?: scalar|Param|null, // A directory that contains multiple certificate authority files.
 *         local_cert?: scalar|Param|null, // A PEM formatted certificate file.
 *         local_pk?: scalar|Param|null, // A private key file.
 *         passphrase?: scalar|Param|null, // The passphrase used to encrypt the "local_pk" file.
 *         ciphers?: scalar|Param|null, // A list of TLS ciphers separated by colons, commas or spaces (e.g. "RC3-SHA:TLS13-AES-128-GCM-SHA256"...).
 *         peer_fingerprint?: array{ // Associative array: hashing algorithm => hash(es).
 *             sha1?: mixed,
 *             pin-sha256?: mixed,
 *             md5?: mixed,
 *         },
 *         crypto_method?: scalar|Param|null, // The minimum version of TLS to accept; must be one of STREAM_CRYPTO_METHOD_TLSv*_CLIENT constants.
 *         extra?: array<string, mixed>,
 *         rate_limiter?: scalar|Param|null, // Rate limiter name to use for throttling requests. // Default: null
 *         caching?: bool|array{ // Caching configuration.
 *             enabled?: bool|Param, // Default: false
 *             cache_pool?: string|Param, // The taggable cache pool to use for storing the responses. // Default: "cache.http_client"
 *             shared?: bool|Param, // Indicates whether the cache is shared (public) or private. // Default: true
 *             max_ttl?: int|Param, // The maximum TTL (in seconds) allowed for cached responses. // Default: 86400
 *         },
 *         retry_failed?: bool|array{
 *             enabled?: bool|Param, // Default: false
 *             base_uris?: Param|string|list<string|Param>,
 *             retry_strategy?: scalar|Param|null, // service id to override the retry strategy. // Default: null
 *             http_codes?: Param|int|string|array<string, array{ // Default: []
 *                 code?: int|Param,
 *                 methods?: Param|string|list<string|Param>,
 *             }>,
 *             max_retries?: int|Param, // Default: 3
 *             delay?: int|Param, // Time in ms to delay (or the initial value when multiplier is used). // Default: 1000
 *             multiplier?: float|Param, // If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries). // Default: 2
 *             max_delay?: int|Param, // Max time in ms that a retry should ever be delayed (0 = infinite). // Default: 0
 *             jitter?: float|Param, // Randomness in percent (between 0 and 1) to apply to the delay. // Default: 0.1
 *         },
 *     }>,
 * }
 * @psalm-type FrameworkConfig = array{
 *     secret?: scalar|Param|null, // A secret that must not change. The options that sign data default to it and take a list of secrets to rotate theirs.
 *     http_method_override?: bool|Param, // Set true to enable support for the '_method' request parameter to determine the intended HTTP method on POST requests. // Default: false
 *     allowed_http_method_override?: null|list<string|Param>,
 *     trust_x_sendfile_type_header?: scalar|Param|null, // Set true to enable support for xsendfile in binary file responses. // Default: "%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%"
 *     ide?: scalar|Param|null, // Deprecated: Setting the "framework.ide.ide" configuration option is deprecated, use the "SYMFONY_IDE" env var instead. // Default: null
 *     test?: bool|Param,
 *     default_locale?: scalar|Param|null, // Default: "en"
 *     set_locale_from_accept_language?: bool|Param, // Whether to use the Accept-Language HTTP header to set the Request locale (only when the "_locale" request attribute is not passed). // Default: false
 *     set_content_language_from_locale?: bool|Param, // Whether to set the Content-Language HTTP header on the Response using the Request locale. // Default: false
 *     enabled_locales?: list<scalar|Param|null>,
 *     trusted_hosts?: Param|string|list<scalar|Param|null>,
 *     trusted_proxies?: mixed, // Default: ["%env(default::SYMFONY_TRUSTED_PROXIES)%"]
 *     trusted_headers?: Param|string|list<scalar|Param|null>,
 *     error_controller?: scalar|Param|null, // Default: "error_controller"
 *     handle_all_throwables?: bool|Param, // HttpKernel will handle all kinds of \Throwable. // Default: true
 *     csrf_protection?: bool|array{
 *         enabled?: scalar|Param|null, // Default: null
 *         stateless_token_ids?: list<scalar|Param|null>,
 *         check_header?: scalar|Param|null, // Whether to check the CSRF token in a header in addition to a cookie when using stateless protection. // Default: false
 *         cookie_name?: scalar|Param|null, // The name of the cookie to use when using stateless protection. // Default: "csrf-token"
 *     },
 *     form?: bool|array{ // Form configuration
 *         enabled?: bool|Param, // Default: true
 *         csrf_protection?: bool|array{
 *             enabled?: scalar|Param|null, // Default: null
 *             token_id?: scalar|Param|null, // Default: null
 *             field_name?: scalar|Param|null, // Default: "_token"
 *             field_attr?: array<string, scalar|Param|null>,
 *         },
 *     },
 *     http_cache?: bool|array{ // HTTP cache configuration
 *         enabled?: bool|Param, // Default: false
 *         debug?: bool|Param, // Default: "%kernel.debug%"
 *         trace_level?: "none"|"short"|"full"|Param,
 *         trace_header?: scalar|Param|null,
 *         cache_status?: scalar|Param|null, // Enables the RFC 9211 "Cache-Status" response header and names this cache in it, e.g. "Symfony". No header is added when null.
 *         default_ttl?: int|Param,
 *         private_headers?: list<scalar|Param|null>,
 *         skip_response_headers?: list<scalar|Param|null>,
 *         allow_reload?: bool|Param,
 *         allow_revalidate?: bool|Param,
 *         stale_while_revalidate?: int|Param,
 *         stale_if_error?: int|Param,
 *         terminate_on_cache_hit?: bool|Param, // Deprecated: Setting the "framework.http_cache.terminate_on_cache_hit.terminate_on_cache_hit" configuration option is deprecated. It will be removed in version 9.0.
 *     },
 *     esi?: bool|array{ // ESI configuration
 *         enabled?: bool|Param, // Default: false
 *     },
 *     ssi?: bool|array{ // SSI configuration
 *         enabled?: bool|Param, // Default: false
 *     },
 *     fragments?: bool|array{ // Fragments configuration
 *         enabled?: bool|Param, // Default: false
 *         hinclude_default_template?: scalar|Param|null, // Deprecated: Setting the "framework.fragments.hinclude_default_template.hinclude_default_template" configuration option is deprecated. It will be removed in version 9.0. // Default: null
 *         path?: scalar|Param|null, // Default: "/_fragment"
 *     },
 *     uri_signer?: array{ // URI signer configuration
 *         secret?: Param|string|list<scalar|Param|null>,
 *         expiration?: int|Param, // Default expiration of signed URIs, in seconds. // Default: null
 *     },
 *     profiler?: bool|array{ // Profiler configuration
 *         enabled?: bool|Param, // Default: false
 *         collect?: bool|Param, // Default: true
 *         collect_parameter?: scalar|Param|null, // The name of the parameter to use to enable or disable collection on a per request basis. // Default: null
 *         only_exceptions?: bool|Param, // Default: false
 *         only_main_requests?: bool|Param, // Default: false
 *         excluded_paths?: Param|string|list<scalar|Param|null>,
 *         excluded_http_codes?: Param|int|string|list<Param|string|list<scalar|Param|null>>,
 *         dsn?: scalar|Param|null, // Default: "file:%kernel.cache_dir%/profiler"
 *         collect_serializer_data?: true|Param, // Deprecated: Setting the "framework.profiler.collect_serializer_data.collect_serializer_data" configuration option is deprecated. It will be removed in version 9.0. // Default: true
 *     },
 *     workflows?: WorkflowConfig,
 *     router?: RouterConfig,
 *     assets?: AssetConfig,
 *     asset_mapper?: AssetMapperConfig,
 *     translator?: TranslationConfig,
 *     validation?: ValidationConfig,
 *     serializer?: SerializerConfig,
 *     property_access?: PropertyAccessConfig,
 *     type_info?: TypeInfoConfig,
 *     property_info?: PropertyInfoConfig,
 *     cache?: CacheConfig,
 *     web_link?: mixed,
 *     lock?: LockConfig,
 *     semaphore?: mixed,
 *     messenger?: mixed,
 *     scheduler?: mixed,
 *     http_client?: HttpClientConfig,
 *     mailer?: mixed,
 *     notifier?: mixed,
 *     rate_limiter?: mixed,
 *     uid?: UidConfig,
 *     html_sanitizer?: mixed,
 *     webhook?: mixed,
 *     remote_event?: mixed,
 *     json_streamer?: mixed,
 *     session?: bool|array{ // Session configuration
 *         enabled?: bool|Param, // Default: false
 *         storage_factory_id?: scalar|Param|null, // Defaults to "session.storage.factory.mock_file" when "framework.test" is true, to "session.storage.factory.native" otherwise. // Default: null
 *         handler_id?: scalar|Param|null, // Defaults to using the native session handler, or to the native *file* session handler if "save_path" is not null.
 *         name?: scalar|Param|null,
 *         cookie_lifetime?: scalar|Param|null,
 *         cookie_path?: scalar|Param|null,
 *         cookie_domain?: scalar|Param|null,
 *         cookie_secure?: true|false|"auto"|Param, // Default: "auto"
 *         cookie_httponly?: bool|Param, // Default: true
 *         cookie_samesite?: null|"lax"|"strict"|"none"|Param, // Default: "lax"
 *         use_cookies?: bool|Param,
 *         gc_divisor?: scalar|Param|null,
 *         gc_probability?: scalar|Param|null,
 *         gc_maxlifetime?: scalar|Param|null,
 *         save_path?: scalar|Param|null, // Defaults to "%kernel.cache_dir%/sessions" if the "handler_id" option is not null.
 *         metadata_update_threshold?: int|Param, // Seconds to wait between 2 session metadata updates. // Default: 0
 *         isolate_attributes?: bool|Param, // Whether to deep-clone the values read from and passed to session attributes, so that only values passed to set() are saved. // Default: false
 *     },
 *     request?: bool|array{ // Request configuration
 *         enabled?: bool|Param, // Default: false
 *         formats?: array<string, Param|string|list<scalar|Param|null>>,
 *         serializer?: scalar|Param|null, // Service id of the serializer that maps request payloads and query strings to controller arguments with #[MapRequestPayload] and #[MapQueryString], e.g. "serializer.api" for the named serializer "api". // Default: null
 *     },
 *     response?: array{ // Response configuration
 *         serializer?: scalar|Param|null, // Service id of the serializer that serializes the values returned by controllers with #[Serialize], e.g. "serializer.api" for the named serializer "api". // Default: null
 *     },
 *     php_errors?: array{ // PHP errors handling configuration
 *         log?: mixed, // Use the application logger instead of the PHP logger for logging PHP errors. // Default: true
 *         throw?: bool|Param|null, // Throw PHP errors as \ErrorException instances. Enabled by default when debug is enabled. // Default: null
 *     },
 *     exceptions?: array<string, array{ // Default: []
 *         log_level?: scalar|Param|null, // The level of log message. Null to let Symfony decide. // Default: null
 *         status_code?: scalar|Param|null, // The status code of the response. Null or 0 to let Symfony decide. // Default: null
 *         log_channel?: scalar|Param|null, // The channel of log message. Null to let Symfony decide. // Default: null
 *     }>,
 *     disallow_search_engine_index?: bool|Param|null, // Enabled by default when debug is enabled. // Default: null
 *     secrets?: bool|array{
 *         enabled?: bool|Param, // Default: true
 *         vault_directory?: scalar|Param|null, // Default: "%kernel.project_dir%/config/secrets/%kernel.runtime_environment%"
 *         local_dotenv_file?: scalar|Param|null, // Default: "%kernel.project_dir%/.env.%kernel.environment%.local"
 *         decryption_env_var?: scalar|Param|null, // Default: "base64:default::SYMFONY_DECRYPTION_SECRET"
 *     },
 * }
 * @psalm-type DoctrineConfig = array{
 *     dbal?: array{
 *         default_connection?: scalar|Param|null,
 *         types?: array<string, Param|string|array{ // Default: []
 *             class?: scalar|Param|null,
 *         }>,
 *         driver_schemes?: array<string, scalar|Param|null>,
 *         connections?: array<string, array{ // Default: []
 *             url?: scalar|Param|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *             dbname?: scalar|Param|null,
 *             host?: scalar|Param|null, // Defaults to "localhost" at runtime.
 *             port?: scalar|Param|null, // Defaults to null at runtime.
 *             user?: scalar|Param|null, // Defaults to "root" at runtime.
 *             password?: scalar|Param|null, // Defaults to null at runtime.
 *             dbname_suffix?: scalar|Param|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *             application_name?: scalar|Param|null,
 *             charset?: scalar|Param|null,
 *             path?: scalar|Param|null,
 *             memory?: bool|Param,
 *             unix_socket?: scalar|Param|null, // The unix socket to use for MySQL
 *             persistent?: bool|Param, // True to use as persistent connection for the ibm_db2 driver
 *             protocol?: scalar|Param|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *             service?: bool|Param, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *             servicename?: scalar|Param|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *             sessionMode?: scalar|Param|null, // The session mode to use for the oci8 driver
 *             server?: scalar|Param|null, // The name of a running database server to connect to for SQL Anywhere.
 *             default_dbname?: scalar|Param|null, // Override the default database (postgres) to connect to for PostgreSQL connection.
 *             sslmode?: scalar|Param|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *             sslrootcert?: scalar|Param|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *             sslcert?: scalar|Param|null, // The path to the SSL client certificate file for PostgreSQL.
 *             sslkey?: scalar|Param|null, // The path to the SSL client key file for PostgreSQL.
 *             sslcrl?: scalar|Param|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *             pooled?: bool|Param, // True to use a pooled server with the oci8/pdo_oracle driver
 *             MultipleActiveResultSets?: bool|Param, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *             instancename?: scalar|Param|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *             connectstring?: scalar|Param|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *             driver?: scalar|Param|null, // Default: "pdo_mysql"
 *             auto_commit?: bool|Param,
 *             schema_filter?: scalar|Param|null,
 *             logging?: bool|Param, // Default: true
 *             profiling?: bool|Param, // Default: true
 *             profiling_collect_backtrace?: bool|Param, // Enables collecting backtraces when profiling is enabled // Default: false
 *             profiling_collect_schema_errors?: bool|Param, // Enables collecting schema errors when profiling is enabled // Default: true
 *             server_version?: scalar|Param|null,
 *             idle_connection_ttl?: int|Param, // Default: 600
 *             driver_class?: scalar|Param|null,
 *             wrapper_class?: scalar|Param|null,
 *             keep_replica?: bool|Param,
 *             options?: array<string, mixed>,
 *             mapping_types?: array<string, scalar|Param|null>,
 *             default_table_options?: array<string, scalar|Param|null>,
 *             schema_manager_factory?: scalar|Param|null, // Default: "doctrine.dbal.default_schema_manager_factory"
 *             result_cache?: scalar|Param|null,
 *             replicas?: array<string, array{ // Default: []
 *                 url?: scalar|Param|null, // A URL with connection information; any parameter value parsed from this string will override explicitly set parameters
 *                 dbname?: scalar|Param|null,
 *                 host?: scalar|Param|null, // Defaults to "localhost" at runtime.
 *                 port?: scalar|Param|null, // Defaults to null at runtime.
 *                 user?: scalar|Param|null, // Defaults to "root" at runtime.
 *                 password?: scalar|Param|null, // Defaults to null at runtime.
 *                 dbname_suffix?: scalar|Param|null, // Adds the given suffix to the configured database name, this option has no effects for the SQLite platform
 *                 application_name?: scalar|Param|null,
 *                 charset?: scalar|Param|null,
 *                 path?: scalar|Param|null,
 *                 memory?: bool|Param,
 *                 unix_socket?: scalar|Param|null, // The unix socket to use for MySQL
 *                 persistent?: bool|Param, // True to use as persistent connection for the ibm_db2 driver
 *                 protocol?: scalar|Param|null, // The protocol to use for the ibm_db2 driver (default to TCPIP if omitted)
 *                 service?: bool|Param, // True to use SERVICE_NAME as connection parameter instead of SID for Oracle
 *                 servicename?: scalar|Param|null, // Overrules dbname parameter if given and used as SERVICE_NAME or SID connection parameter for Oracle depending on the service parameter.
 *                 sessionMode?: scalar|Param|null, // The session mode to use for the oci8 driver
 *                 server?: scalar|Param|null, // The name of a running database server to connect to for SQL Anywhere.
 *                 default_dbname?: scalar|Param|null, // Override the default database (postgres) to connect to for PostgreSQL connection.
 *                 sslmode?: scalar|Param|null, // Determines whether or with what priority a SSL TCP/IP connection will be negotiated with the server for PostgreSQL.
 *                 sslrootcert?: scalar|Param|null, // The name of a file containing SSL certificate authority (CA) certificate(s). If the file exists, the server's certificate will be verified to be signed by one of these authorities.
 *                 sslcert?: scalar|Param|null, // The path to the SSL client certificate file for PostgreSQL.
 *                 sslkey?: scalar|Param|null, // The path to the SSL client key file for PostgreSQL.
 *                 sslcrl?: scalar|Param|null, // The file name of the SSL certificate revocation list for PostgreSQL.
 *                 pooled?: bool|Param, // True to use a pooled server with the oci8/pdo_oracle driver
 *                 MultipleActiveResultSets?: bool|Param, // Configuring MultipleActiveResultSets for the pdo_sqlsrv driver
 *                 instancename?: scalar|Param|null, // Optional parameter, complete whether to add the INSTANCE_NAME parameter in the connection. It is generally used to connect to an Oracle RAC server to select the name of a particular instance.
 *                 connectstring?: scalar|Param|null, // Complete Easy Connect connection descriptor, see https://docs.oracle.com/database/121/NETAG/naming.htm.When using this option, you will still need to provide the user and password parameters, but the other parameters will no longer be used. Note that when using this parameter, the getHost and getPort methods from Doctrine\DBAL\Connection will no longer function as expected.
 *                 ...<string, mixed>
 *             }>,
 *             ...<string, mixed>
 *         }>,
 *         ...<string, mixed>
 *     },
 *     orm?: array{
 *         default_entity_manager?: scalar|Param|null,
 *         enable_native_lazy_objects?: bool|Param, // Deprecated: The "enable_native_lazy_objects" option is deprecated and will be removed in DoctrineBundle 4.0, as native lazy objects are now always enabled. // Default: true
 *         controller_resolver?: bool|array{
 *             enabled?: bool|Param, // Default: true
 *             auto_mapping?: bool|Param, // Deprecated: The "doctrine.orm.controller_resolver.auto_mapping.auto_mapping" option is deprecated and will be removed in DoctrineBundle 4.0, as it only accepts `false` since 3.0. // Set to true to enable using route placeholders as lookup criteria when the primary key doesn't match the argument name // Default: false
 *             evict_cache?: bool|Param, // Set to true to fetch the entity from the database instead of using the cache, if any // Default: false
 *         },
 *         entity_managers?: array<string, array{ // Default: []
 *             query_cache_driver?: Param|string|array{
 *                 type?: scalar|Param|null, // Default: null
 *                 id?: scalar|Param|null,
 *                 pool?: scalar|Param|null,
 *             },
 *             metadata_cache_driver?: Param|string|array{
 *                 type?: scalar|Param|null, // Default: null
 *                 id?: scalar|Param|null,
 *                 pool?: scalar|Param|null,
 *             },
 *             result_cache_driver?: Param|string|array{
 *                 type?: scalar|Param|null, // Default: null
 *                 id?: scalar|Param|null,
 *                 pool?: scalar|Param|null,
 *             },
 *             entity_listeners?: array{
 *                 entities?: array<string, array{ // Default: []
 *                     listeners?: array<string, array{ // Default: []
 *                         events?: list<array{ // Default: []
 *                             type?: scalar|Param|null,
 *                             method?: scalar|Param|null, // Default: null
 *                         }>,
 *                     }>,
 *                 }>,
 *                 ...<string, mixed>
 *             },
 *             connection?: scalar|Param|null,
 *             class_metadata_factory_name?: scalar|Param|null, // Default: "Doctrine\\ORM\\Mapping\\ClassMetadataFactory"
 *             default_repository_class?: scalar|Param|null, // Default: "Doctrine\\ORM\\EntityRepository"
 *             auto_mapping?: scalar|Param|null, // Default: false
 *             naming_strategy?: scalar|Param|null, // Default: "doctrine.orm.naming_strategy.default"
 *             quote_strategy?: scalar|Param|null, // Default: "doctrine.orm.quote_strategy.default"
 *             typed_field_mapper?: scalar|Param|null, // Default: "doctrine.orm.typed_field_mapper.default"
 *             entity_listener_resolver?: scalar|Param|null, // Default: null
 *             fetch_mode_subselect_batch_size?: scalar|Param|null,
 *             repository_factory?: scalar|Param|null, // Default: "doctrine.orm.container_repository_factory"
 *             schema_ignore_classes?: list<scalar|Param|null>,
 *             validate_xml_mapping?: bool|Param, // Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.14 and will be mandatory in ORM 3.0. See https://github.com/doctrine/orm/pull/6728. // Default: false
 *             second_level_cache?: array{
 *                 region_cache_driver?: Param|string|array{
 *                     type?: scalar|Param|null, // Default: null
 *                     id?: scalar|Param|null,
 *                     pool?: scalar|Param|null,
 *                 },
 *                 region_lock_lifetime?: scalar|Param|null, // Default: 60
 *                 log_enabled?: bool|Param, // Default: true
 *                 region_lifetime?: scalar|Param|null, // Default: 3600
 *                 enabled?: bool|Param, // Default: true
 *                 factory?: scalar|Param|null,
 *                 regions?: array<string, array{ // Default: []
 *                     cache_driver?: Param|string|array{
 *                         type?: scalar|Param|null, // Default: null
 *                         id?: scalar|Param|null,
 *                         pool?: scalar|Param|null,
 *                     },
 *                     lock_path?: scalar|Param|null, // Default: "%kernel.cache_dir%/doctrine/orm/slc/filelock"
 *                     lock_lifetime?: scalar|Param|null, // Default: 60
 *                     type?: scalar|Param|null, // Default: "default"
 *                     lifetime?: scalar|Param|null, // Default: null
 *                     service?: scalar|Param|null,
 *                     name?: scalar|Param|null,
 *                 }>,
 *                 loggers?: array<string, array{ // Default: []
 *                     name?: scalar|Param|null,
 *                     service?: scalar|Param|null,
 *                 }>,
 *             },
 *             hydrators?: array<string, scalar|Param|null>,
 *             mappings?: array<string, Param|bool|string|array{ // Default: []
 *                 mapping?: scalar|Param|null, // Default: true
 *                 type?: scalar|Param|null,
 *                 dir?: scalar|Param|null,
 *                 alias?: scalar|Param|null,
 *                 prefix?: scalar|Param|null,
 *                 is_bundle?: bool|Param,
 *             }>,
 *             dql?: array{
 *                 string_functions?: array<string, scalar|Param|null>,
 *                 numeric_functions?: array<string, scalar|Param|null>,
 *                 datetime_functions?: array<string, scalar|Param|null>,
 *             },
 *             filters?: array<string, Param|string|array{ // Default: []
 *                 class?: scalar|Param|null,
 *                 enabled?: bool|Param, // Default: false
 *                 parameters?: array<string, mixed>,
 *                 ...<string, mixed>
 *             }>,
 *             identity_generation_preferences?: array<string, scalar|Param|null>,
 *         }>,
 *         resolve_target_entities?: array<string, scalar|Param|null>,
 *         ...<string, mixed>
 *     },
 * }
 * @psalm-type DoctrineMigrationsConfig = array{
 *     enable_service_migrations?: bool|Param, // Whether to enable fetching migrations from the service container. // Default: false
 *     migrations_paths?: array<string, scalar|Param|null>,
 *     services?: array<string, scalar|Param|null>,
 *     factories?: array<string, scalar|Param|null>,
 *     storage?: array{ // Storage to use for migration status metadata.
 *         table_storage?: array{ // The default metadata storage, implemented as a table in the database.
 *             table_name?: scalar|Param|null, // Default: null
 *             version_column_name?: scalar|Param|null, // Default: null
 *             version_column_length?: scalar|Param|null, // Default: null
 *             executed_at_column_name?: scalar|Param|null, // Default: null
 *             execution_time_column_name?: scalar|Param|null, // Default: null
 *         },
 *     },
 *     migrations?: list<scalar|Param|null>,
 *     connection?: scalar|Param|null, // Connection name to use for the migrations database. // Default: null
 *     em?: scalar|Param|null, // Entity manager name to use for the migrations database (available when doctrine/orm is installed). // Default: null
 *     all_or_nothing?: scalar|Param|null, // Run all migrations in a transaction. // Default: false
 *     check_database_platform?: scalar|Param|null, // Adds an extra check in the generated migrations to allow execution only on the same platform as they were initially generated on. // Default: true
 *     custom_template?: scalar|Param|null, // Custom template path for generated migration classes. // Default: null
 *     organize_migrations?: scalar|Param|null, // Organize migrations mode. Possible values are: "BY_YEAR", "BY_YEAR_AND_MONTH", false // Default: false
 *     enable_profiler?: bool|Param, // Whether or not to enable the profiler collector to calculate and visualize migration status. This adds some queries overhead. // Default: false
 *     transactional?: bool|Param, // Whether or not to wrap migrations in a single transaction. // Default: true
 * }
 * @psalm-type TwigConfig = array{
 *     form_themes?: list<scalar|Param|null>,
 *     globals?: array<string, array{ // Default: []
 *         id?: scalar|Param|null,
 *         type?: scalar|Param|null,
 *         value?: mixed,
 *         ...<string, mixed>
 *     }>,
 *     autoescape_service?: scalar|Param|null, // Default: null
 *     autoescape_service_method?: scalar|Param|null, // Default: null
 *     cache?: scalar|Param|null, // Default: true
 *     charset?: scalar|Param|null, // Default: "%kernel.charset%"
 *     debug?: bool|Param, // Default: "%kernel.debug%"
 *     strict_variables?: bool|Param, // Default: "%kernel.debug%"
 *     auto_reload?: scalar|Param|null,
 *     optimizations?: int|Param,
 *     default_path?: scalar|Param|null, // The default path used to load templates. // Default: "%kernel.project_dir%/templates"
 *     file_name_pattern?: Param|string|list<scalar|Param|null>,
 *     paths?: array<string, mixed>,
 *     date?: array{ // The default format options used by the date filter.
 *         format?: scalar|Param|null, // Default: "F j, Y H:i"
 *         interval_format?: scalar|Param|null, // Default: "%d days"
 *         timezone?: scalar|Param|null, // The timezone used when formatting dates, when set to null, the timezone returned by date_default_timezone_get() is used. // Default: null
 *     },
 *     number_format?: array{ // The default format options for the number_format filter.
 *         decimals?: int|Param, // Default: 0
 *         decimal_point?: scalar|Param|null, // Default: "."
 *         thousands_separator?: scalar|Param|null, // Default: ","
 *     },
 *     mailer?: array{
 *         html_to_text_converter?: scalar|Param|null, // A service implementing the "Symfony\Component\Mime\HtmlToTextConverter\HtmlToTextConverterInterface". // Default: null
 *     },
 * }
 * @psalm-type TwigExtraConfig = array{
 *     cache?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *     },
 *     html?: bool|array{
 *         enabled?: bool|Param, // Default: true
 *     },
 *     markdown?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *     },
 *     intl?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *     },
 *     cssinliner?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *     },
 *     inky?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *     },
 *     string?: bool|array{
 *         enabled?: bool|Param, // Default: false
 *     },
 *     commonmark?: array{
 *         renderer?: array{ // Array of options for rendering HTML.
 *             block_separator?: scalar|Param|null,
 *             inner_separator?: scalar|Param|null,
 *             soft_break?: scalar|Param|null,
 *         },
 *         html_input?: "strip"|"allow"|"escape"|Param, // How to handle HTML input.
 *         allow_unsafe_links?: bool|Param, // Remove risky link and image URLs by setting this to false. // Default: true
 *         max_nesting_level?: int|Param, // The maximum nesting level for blocks. // Default: 9223372036854775807
 *         max_delimiters_per_line?: int|Param, // The maximum number of strong/emphasis delimiters per line. // Default: 9223372036854775807
 *         slug_normalizer?: array{ // Array of options for configuring how URL-safe slugs are created.
 *             instance?: mixed,
 *             max_length?: int|Param, // Default: 255
 *             unique?: mixed,
 *         },
 *         commonmark?: array{ // Array of options for configuring the CommonMark core extension.
 *             enable_em?: bool|Param, // Default: true
 *             enable_strong?: bool|Param, // Default: true
 *             use_asterisk?: bool|Param, // Default: true
 *             use_underscore?: bool|Param, // Default: true
 *             unordered_list_markers?: list<scalar|Param|null>,
 *         },
 *         ...<string, mixed>
 *     },
 * }
 * @psalm-type TwigComponentConfig = array{
 *     defaults?: array<string, Param|string|array{ // Default: []
 *         template_directory?: scalar|Param|null, // Default: "components"
 *         name_prefix?: scalar|Param|null, // Default: ""
 *     }>,
 *     anonymous_template_directory?: scalar|Param|null, // Defaults to `components`
 *     profiler?: bool|array{ // Enables the profiler for Twig Component
 *         enabled?: bool|Param, // Default: "%kernel.debug%"
 *         collect_components?: bool|Param, // Collect components instances // Default: true
 *     },
 * }
 * @psalm-type SecurityConfig = array{
 *     access_denied_url?: scalar|Param|null, // Default: null
 *     recent_authentication_lifetime?: int|Param, // Number of seconds an interactive authentication keeps granting IS_AUTHENTICATED_RECENTLY. Use it to make sensitive actions require the user to authenticate again. // Default: 7200
 *     very_recent_authentication_lifetime?: int|Param, // Number of seconds an interactive authentication keeps granting IS_AUTHENTICATED_VERY_RECENTLY, a stricter bar than IS_AUTHENTICATED_RECENTLY for the most sensitive actions. // Default: 300
 *     session_fixation_strategy?: "none"|"migrate"|"invalidate"|Param, // Default: "migrate"
 *     expose_security_errors?: \Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::None|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::AccountStatus|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::All|Param, // Default: "none"
 *     erase_credentials?: bool|Param, // Deprecated: Setting the "security.erase_credentials.erase_credentials" configuration option is deprecated. It will be removed in Symfony 9.0, as the "eraseCredentials()" method was removed in Symfony 8.0. // Default: true
 *     access_decision_manager?: array{
 *         strategy?: "affirmative"|"consensus"|"unanimous"|"priority"|Param,
 *         service?: scalar|Param|null,
 *         strategy_service?: scalar|Param|null,
 *         allow_if_all_abstain?: bool|Param, // Default: false
 *         allow_if_equal_granted_denied?: bool|Param, // Default: true
 *     },
 *     password_hashers?: array<string, Param|string|array{ // Default: []
 *         algorithm?: scalar|Param|null,
 *         migrate_from?: Param|string|list<scalar|Param|null>,
 *         hash_algorithm?: scalar|Param|null, // Name of hashing algorithm for PBKDF2 (i.e. sha256, sha512, etc..) See hash_algos() for a list of supported algorithms. // Default: "sha512"
 *         key_length?: scalar|Param|null, // Default: 40
 *         ignore_case?: bool|Param, // Default: false
 *         encode_as_base64?: bool|Param, // Default: true
 *         iterations?: scalar|Param|null, // Default: 5000
 *         cost?: int|Param, // Defaults to the lowest value bcrypt accepts when "framework.test" is enabled. // Default: null
 *         memory_cost?: scalar|Param|null, // Defaults to the lowest value argon2 accepts when "framework.test" is enabled. // Default: null
 *         time_cost?: scalar|Param|null, // Defaults to the lowest value argon2 accepts when "framework.test" is enabled. // Default: null
 *         id?: scalar|Param|null,
 *     }>,
 *     providers?: array<string, array{ // Default: []
 *         id?: scalar|Param|null,
 *         chain?: array{
 *             providers?: Param|string|list<scalar|Param|null>,
 *         },
 *         entity?: array{
 *             class?: scalar|Param|null, // The full entity class name of your user class.
 *             property?: scalar|Param|null, // Default: null
 *             manager_name?: scalar|Param|null, // Default: null
 *         },
 *         memory?: array{
 *             users?: array<string, array{ // Default: []
 *                 password?: scalar|Param|null, // Default: null
 *                 roles?: Param|string|list<scalar|Param|null>,
 *             }>,
 *         },
 *         ldap?: array{
 *             service?: scalar|Param|null,
 *             base_dn?: scalar|Param|null,
 *             search_dn?: scalar|Param|null, // Default: null
 *             search_password?: scalar|Param|null, // Default: null
 *             extra_fields?: list<scalar|Param|null>,
 *             default_roles?: Param|string|list<scalar|Param|null>,
 *             role_fetcher?: scalar|Param|null, // Default: null
 *             uid_key?: scalar|Param|null, // Default: "sAMAccountName"
 *             filter?: scalar|Param|null, // Default: "({uid_key}={user_identifier})"
 *             password_attribute?: scalar|Param|null, // Default: null
 *         },
 *         oidc?: array{
 *             enabled?: bool|Param, // Internal marker; the OIDC provider has no configuration options. // Default: true
 *             ...<string, mixed>
 *         },
 *     }>,
 *     firewalls?: array<string, array{ // Default: []
 *         pattern?: scalar|Param|null,
 *         host?: scalar|Param|null,
 *         methods?: Param|string|list<scalar|Param|null>,
 *         security?: bool|Param, // Default: true
 *         user_checker?: scalar|Param|null, // The UserChecker to use when authenticating users in this firewall. // Default: "security.user_checker"
 *         user_checker_on_refresh?: bool|Param, // Whether to run this firewall's UserChecker again when the user is refreshed from the session, so that an account disabled during the session is rejected on the next request. It then runs on every request of this firewall, so enable it only if that checker is safe to call that often. // Default: false
 *         request_matcher?: scalar|Param|null,
 *         access_denied_url?: scalar|Param|null,
 *         access_denied_handler?: scalar|Param|null,
 *         entry_point?: scalar|Param|null, // An enabled authenticator name or a service id that implements "Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface".
 *         entry_point_path?: scalar|Param|null, // A path, an absolute URL or a route name to redirect anonymous users to when they need to authenticate.
 *         re_authentication_entry_point?: scalar|Param|null, // An enabled authenticator name or a service id implementing "Symfony\Component\Security\Http\EntryPoint\ReAuthenticationEntryPointInterface", started when the vote denying access asks for a re-authentication. Defaults to the firewall entry point when it implements this interface.
 *         provider?: scalar|Param|null,
 *         stateless?: bool|Param, // Default: false
 *         lazy?: bool|Param, // Default: false
 *         context?: scalar|Param|null,
 *         logout?: array{
 *             enable_csrf?: bool|Param|null, // Default: null
 *             csrf_token_id?: scalar|Param|null, // Default: "logout"
 *             csrf_parameter?: scalar|Param|null, // Default: "_csrf_token"
 *             csrf_token_manager?: scalar|Param|null,
 *             path?: scalar|Param|null, // Default: "/logout"
 *             target?: scalar|Param|null, // Default: "/"
 *             invalidate_session?: bool|Param, // Default: true
 *             clear_site_data?: Param|string|list<"*"|"cache"|"cookies"|"storage"|"clientHints"|"executionContexts"|"prefetchCache"|"prerenderCache"|Param>,
 *             delete_cookies?: Param|string|array<string, array{ // Default: []
 *                 path?: scalar|Param|null, // Default: null
 *                 domain?: scalar|Param|null, // Default: null
 *                 secure?: scalar|Param|null, // Default: false
 *                 samesite?: scalar|Param|null, // Default: null
 *                 partitioned?: scalar|Param|null, // Default: false
 *             }>,
 *         },
 *         switch_user?: array{
 *             provider?: scalar|Param|null,
 *             parameter?: scalar|Param|null, // Default: "_switch_user"
 *             role?: scalar|Param|null, // Default: "ROLE_ALLOWED_TO_SWITCH"
 *             target_route?: scalar|Param|null, // Default: null
 *             path?: scalar|Param|null, // Restrict user switching to this path (a path or route name). Declaring the route POST-only is up to the application. The parameter is no longer read from the request headers in this mode. // Default: null
 *             enable_csrf?: bool|Param|null, // Default: null
 *             csrf_token_id?: scalar|Param|null, // Default: "switch_user"
 *             csrf_parameter?: scalar|Param|null, // Default: "_csrf_token"
 *             csrf_token_manager?: scalar|Param|null,
 *         },
 *         required_badges?: list<scalar|Param|null>,
 *         custom_authenticators?: list<scalar|Param|null>,
 *         login_throttling?: array{
 *             limiter?: scalar|Param|null, // A service id implementing "Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface".
 *             max_attempts?: int|Param, // Default: 5
 *             interval?: scalar|Param|null, // Default: "1 minute"
 *             lock_factory?: scalar|Param|null, // The service ID of the lock factory used by the login rate limiter ("auto" to use the default one when the Lock component is configured, or null to disable locking). // Default: "auto"
 *             cache_pool?: string|Param, // The cache pool to use for storing the limiter state // Default: "cache.rate_limiter"
 *             storage_service?: string|Param, // The service ID of a custom storage implementation, this precedes any configured "cache_pool" // Default: null
 *         },
 *         x509?: array{
 *             provider?: scalar|Param|null,
 *             user?: scalar|Param|null, // Default: "SSL_CLIENT_S_DN_Email"
 *             credentials?: scalar|Param|null, // Default: "SSL_CLIENT_S_DN"
 *             user_identifier?: scalar|Param|null, // Default: "emailAddress"
 *         },
 *         remote_user?: array{
 *             provider?: scalar|Param|null,
 *             user?: scalar|Param|null, // Default: "REMOTE_USER"
 *         },
 *         login_link?: array{
 *             check_route?: scalar|Param|null, // Route that will validate the login link - e.g. "app_login_link_verify".
 *             check_post_only?: scalar|Param|null, // If true, only HTTP POST requests to "check_route" will be handled by the authenticator. // Default: false
 *             signature_properties?: list<scalar|Param|null>,
 *             lifetime?: int|Param, // The lifetime of the login link in seconds. // Default: 600
 *             max_uses?: int|Param, // Max number of times a login link can be used - null means unlimited within lifetime. // Default: null
 *             used_link_cache?: scalar|Param|null, // Cache service id used to expired links of max_uses is set.
 *             success_handler?: scalar|Param|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface.
 *             failure_handler?: scalar|Param|null, // A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface.
 *             provider?: scalar|Param|null, // The user provider to load users from.
 *             secret?: Param|string|list<scalar|Param|null>,
 *             always_use_default_target_path?: bool|Param, // Default: false
 *             default_target_path?: scalar|Param|null, // Default: "/"
 *             login_path?: scalar|Param|null, // Default: "/login"
 *             target_path_parameter?: scalar|Param|null, // Default: "_target_path"
 *             use_referer?: bool|Param, // Default: false
 *             failure_path?: scalar|Param|null, // Default: null
 *             failure_forward?: bool|Param, // Default: false
 *             failure_path_parameter?: scalar|Param|null, // Default: "_failure_path"
 *         },
 *         oidc_login?: array{
 *             provider?: scalar|Param|null,
 *             remember_me?: bool|Param, // Deprecated: Setting the "security.firewalls..oidc_login.remember_me.remember_me" configuration option has no effect and is deprecated. It will be removed in Symfony 9.0. // Default: true
 *             success_handler?: scalar|Param|null,
 *             failure_handler?: scalar|Param|null,
 *             check_path?: scalar|Param|null, // The firewall path where the OIDC provider redirects after authentication. Must match a redirect URI registered with the provider. A route is declared for this path by the "security.route_loader" service, which the application must import. A route name is accepted too, in which case no route is declared for it. // Default: "/oidc/callback"
 *             use_forward?: bool|Param, // Default: false
 *             login_path?: scalar|Param|null, // Default: "/login"
 *             always_use_default_target_path?: bool|Param, // Default: false
 *             default_target_path?: scalar|Param|null, // Default: "/"
 *             target_path_parameter?: scalar|Param|null, // Default: "_target_path"
 *             use_referer?: bool|Param, // Default: false
 *             failure_path?: scalar|Param|null, // Default: null
 *             failure_forward?: bool|Param, // Default: false
 *             failure_path_parameter?: scalar|Param|null, // Default: "_failure_path"
 *             provider_uri?: scalar|Param|null, // The OIDC Issuer URL (e.g. "https://accounts.example.com"). Used for .well-known/openid-configuration discovery unless "discovery_uri" is set.
 *             discovery_uri?: scalar|Param|null, // Absolute URL of the discovery document. The "issuer" it announces is still checked against "provider_uri". // Default: null
 *             http_client?: scalar|Param|null, // The id of the HttpClient service every call to the provider is made with: discovery, JWKS, token and UserInfo endpoints. Defaults to "http_client". A scoped client must scope the discovery host and every host the provider announces. // Default: null
 *             client_id?: scalar|Param|null, // The OIDC client identifier.
 *             client_certificate?: Param|string|array{ // The TLS client certificate presented to the token and UserInfo endpoints.
 *                 certificate?: scalar|Param|null, // Path to the PEM file of the certificate.
 *                 key?: scalar|Param|null, // Path to the PEM file of the private key, when not in the certificate file. // Default: null
 *                 passphrase?: scalar|Param|null, // Passphrase of the private key. // Default: null
 *             },
 *             client_authentication?: Param|string|array{ // How the client authenticates at the token endpoint, which RFC 7591, Section 2 names in its "token_endpoint_auth_method" metadata. Set the method Symfony ships with its parameters, or the id of a service implementing "Symfony\Component\Security\Http\OAuth2\ClientAuthentication\ClientAuthenticationInterface" for a scheme it does not. Exactly one of them.
 *                 client_secret_basic?: scalar|Param|null, // Send the client secret as HTTP Basic credentials, the "client_secret_basic" method of RFC 6749, Section 2.3.1, which the RFC recommends. Takes the client secret.
 *                 client_secret_post?: scalar|Param|null, // Send the client secret in the body of the token request, the "client_secret_post" method of RFC 6749, Section 2.3.1. Takes the client secret. Use it for the providers that support nothing else.
 *                 none?: bool|Param, // Declare a public client (a SPA, a mobile or a native application), which holds no secret and relies on PKCE to protect the code exchange. It can disable neither PKCE nor the ID token signature check.
 *                 client_secret_jwt?: Param|string|array{ // Authenticate with a JWT assertion keyed with the client secret, the "client_secret_jwt" method of OIDC Core 1.0, Section 9. The secret keys an HMAC and is never sent, but the provider holds it too and could sign an assertion in the name of the client: prefer "private_key_jwt", which nobody but the client can sign. Takes the client secret, or a mapping to also set "algorithm" and "lifetime".
 *                     secret?: scalar|Param|null, // The client secret, whose octets key the HMAC. It must be at least as long as the digest the algorithm produces, which RFC 7518, Section 3.2 requires and the algorithm itself checks: 32 bytes for "HS256", 48 for "HS384", 64 for "HS512".
 *                     algorithm?: "HS256"|"HS384"|"HS512"|Param, // The MAC algorithm the assertion is signed with, which must be one your provider announces in "token_endpoint_auth_signing_alg_values_supported". // Default: "HS256"
 *                     lifetime?: int|Param, // How long an assertion is valid, in seconds. It is built for one request and sent right away, so keep it short: it is the window a provider that does not track the "jti" would accept a captured assertion in. // Default: 60
 *                     audience?: "issuer"|"token_endpoint"|Param, // The audience of the assertion: the issuer of the provider, or its token endpoint for a provider that refuses the issuer. // Default: "issuer"
 *                 },
 *                 private_key_jwt?: Param|string|array{ // Authenticate with a JWT assertion signed with the private key of the client, the "private_key_jwt" method of OIDC Core 1.0, Section 9, and the one FAPI 2.0 asks for. The provider only holds the public half, registered as the client "jwks" or fetched from its "jwks_uri", so it learns nothing it could authenticate as the client with.
 *                     key?: scalar|Param|null, // JSON-encoded JWK of the private key the assertion is signed with. Give it a "kid" when the client publishes several keys, so that the provider knows which one verifies the signature without trying them all.
 *                     algorithm?: "RS256"|"RS384"|"RS512"|"ES256"|"ES384"|"ES512"|"PS256"|"PS384"|"PS512"|Param, // The signature algorithm the assertion is signed with, which must be one your provider announces in "token_endpoint_auth_signing_alg_values_supported". FAPI 2.0 asks for "PS256" or "ES256". // Default: "RS256"
 *                     lifetime?: int|Param, // How long an assertion is valid, in seconds. It is built for one request and sent right away, so keep it short: it is the window a provider that does not track the "jti" would accept a captured assertion in. // Default: 60
 *                     audience?: "issuer"|"token_endpoint"|Param, // The audience of the assertion: the issuer of the provider, or its token endpoint for a provider that refuses the issuer. // Default: "issuer"
 *                 },
 *                 tls_client_auth?: bool|Param, // Authenticate with "client_certificate", issued by a certificate authority.
 *                 self_signed_tls_client_auth?: bool|Param, // Authenticate with "client_certificate", self-signed.
 *                 id?: scalar|Param|null, // The id of a service implementing "ClientAuthenticationInterface", for a scheme Symfony does not ship. The method it reports is only known once it is built, so the rules a public client cannot bend are then checked when a login starts on this firewall instead of while the container compiles.
 *             },
 *             dpop?: Param|string|array{ // Binds the tokens the provider issues to a key held by this client (DPoP); a token the provider did not bind is refused.
 *                 key?: scalar|Param|null, // The private key the proofs are signed with, as a JSON-encoded JWK.
 *                 algorithm?: "ES256"|"ES384"|"ES512"|"PS256"|"PS384"|"PS512"|"RS256"|"RS384"|"RS512"|Param, // The algorithm the proofs are signed with, among the "dpop_signing_alg_values_supported" of the provider. // Default: "ES256"
 *             },
 *             scope?: list<scalar|Param|null>,
 *             start_path?: scalar|Param|null, // The path where the route loader declares a route that starts the flow by redirecting to the provider; link to it e.g. from the login page of a firewall offering several ways to log in. A route name is accepted too, in which case no route is declared for it. // Default: "/oidc/start"
 *             discovery_cache_ttl?: int|Param, // TTL in seconds for caching the OIDC discovery configuration, and for the provider JWKS when it advertises no cache lifetime itself. // Default: 3600
 *             allowed_time_drift?: int|Param, // Allowed clock skew in seconds when validating ID token time claims. // Default: 0
 *             user_data_source?: "userinfo"|"id_token"|Param, // Where the user claims are read from: "userinfo" (default) fetches them from the UserInfo endpoint; "id_token" reads them from the validated ID token instead, for providers that put the requested claims there, some of which expose no UserInfo endpoint at all, which is then not required to be announced. // Default: "userinfo"
 *             user_identifier_claim?: scalar|Param|null, // The claim the user identifier is read from. "sub" (default) is the only claim OIDC guarantees stable and unique for the user. Only pick another claim, e.g. "email", when the provider guarantees its value unique, verified and stable too: whoever controls the value of that claim at the provider owns the matching account here. // Default: "sub"
 *             id_token_signature?: array{
 *                 required?: bool|Param, // When true (default), the ID token signature is verified against the provider JWKS. Setting it to false decodes the ID token without verifying it, which OIDC Core 1.0, Section 3.1.3.7, item 6 only allows because the token comes from the token endpoint over TLS: it is then only as safe as the TLS verification of the HTTP client used for that request, so never turn it off with a client configured with "verify_peer: false" or "verify_host: false", nor behind a TLS-terminating proxy. A public client, whose "client_authentication" reports the "none" method, cannot turn it off at all. // Default: true
 *                 algorithms?: list<scalar|Param|null>,
 *                 enforce_key_usage_verification?: bool|Param, // When enabled (default), only keys explicitly designated for signature (via "use":"sig" or a "key_ops" entry containing "sign"/"verify") are accepted. When disabled, keys without any usage designation are also accepted; keys explicitly restricted to encryption are still rejected. // Default: true
 *             },
 *             pkce?: array{
 *                 enabled?: bool|Param, // Whether to use PKCE (Proof Key for Code Exchange, RFC 7636), which any current provider should support; only disable it for one that rejects the "code_challenge" parameter. A public client, whose "client_authentication" reports the "none" method, cannot disable it at all. // Default: true
 *                 method?: "S256"|"plain"|Param, // The PKCE code challenge method. RFC 7636, Section 4.2 mandates "S256" for every client able to compute it, so only ever pick "plain" for a provider that supports nothing else. // Default: "S256"
 *             },
 *             max_age?: int|Param, // Maximum elapsed seconds since the end-user authentication, sent as the "max_age" authorization parameter; the ID token must then carry an "auth_time" claim, which is checked against this value, "allowed_time_drift" included.
 *             authorization_params?: array<string, scalar|Param|null>,
 *             refresh_access_token?: bool|array{ // Renew the access token with the refresh token grant of RFC 6749, Section 6, so that it stays usable to call an API on behalf of the logged-in user. The provider only issues a refresh token when it was asked for one, e.g. with the "offline_access" scope, and the renewal needs the "expires_in" it is optional for the provider to report. Whether this is enabled or not, the tokens are held as the "oauth2_refresh_token", "oauth2_access_token" and "oauth2_access_token_expires_at" attributes of the security token, and the "security.authenticator.oidc_login.token_refresher.<firewall>" service renews them on demand. A provider rotating refresh tokens expects the previous one never to be replayed, which a session handler locking the session guarantees, and the default one does.
 *                 enabled?: bool|Param, // Default: false
 *                 leeway?: int|Param, // How many seconds before its expiry the access token is renewed, so that one handed to a call in flight does not expire on the way. // Default: 30
 *             },
 *             enable_end_session?: bool|Param, // Enable RP-Initiated Logout via the OIDC end_session_endpoint. // Default: false
 *             post_logout_redirect_path?: scalar|Param|null, // Path or route to redirect to after OIDC logout. // Default: "/"
 *             backchannel_logout?: bool|array{ // Accept the logout tokens the OIDC provider posts when one of its sessions ends.
 *                 enabled?: bool|Param, // Default: false
 *                 path?: scalar|Param|null, // Path where the OIDC provider posts its logout tokens. // Default: "/oidc/backchannel-logout"
 *                 cache?: scalar|Param|null, // Id of the cache pool remembering the ended provider sessions, shared by every server of the application. // Default: "cache.security"
 *                 lifetime?: int|Param, // How long an ended provider session is remembered, in seconds; at least how long a session can stay idle. // Default: 86400
 *             },
 *         },
 *         form_login?: array{
 *             provider?: scalar|Param|null,
 *             remember_me?: bool|Param, // Deprecated: Setting the "security.firewalls..form_login.remember_me.remember_me" configuration option has no effect and is deprecated. It will be removed in Symfony 9.0. // Default: true
 *             success_handler?: scalar|Param|null,
 *             failure_handler?: scalar|Param|null,
 *             check_path?: scalar|Param|null, // Default: "/login_check"
 *             use_forward?: bool|Param, // Default: false
 *             login_path?: scalar|Param|null, // Default: "/login"
 *             username_parameter?: scalar|Param|null, // Default: "_username"
 *             password_parameter?: scalar|Param|null, // Default: "_password"
 *             csrf_parameter?: scalar|Param|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|Param|null, // Default: "authenticate"
 *             enable_csrf?: bool|Param, // Default: false
 *             post_only?: bool|Param, // Default: true
 *             form_only?: bool|Param, // Default: false
 *             always_use_default_target_path?: bool|Param, // Default: false
 *             default_target_path?: scalar|Param|null, // Default: "/"
 *             target_path_parameter?: scalar|Param|null, // Default: "_target_path"
 *             use_referer?: bool|Param, // Default: false
 *             failure_path?: scalar|Param|null, // Default: null
 *             failure_forward?: bool|Param, // Default: false
 *             failure_path_parameter?: scalar|Param|null, // Default: "_failure_path"
 *         },
 *         form_login_ldap?: array{
 *             provider?: scalar|Param|null,
 *             remember_me?: bool|Param, // Deprecated: Setting the "security.firewalls..form_login_ldap.remember_me.remember_me" configuration option has no effect and is deprecated. It will be removed in Symfony 9.0. // Default: true
 *             success_handler?: scalar|Param|null,
 *             failure_handler?: scalar|Param|null,
 *             check_path?: scalar|Param|null, // Default: "/login_check"
 *             use_forward?: bool|Param, // Default: false
 *             login_path?: scalar|Param|null, // Default: "/login"
 *             username_parameter?: scalar|Param|null, // Default: "_username"
 *             password_parameter?: scalar|Param|null, // Default: "_password"
 *             csrf_parameter?: scalar|Param|null, // Default: "_csrf_token"
 *             csrf_token_id?: scalar|Param|null, // Default: "authenticate"
 *             enable_csrf?: bool|Param, // Default: false
 *             post_only?: bool|Param, // Default: true
 *             form_only?: bool|Param, // Default: false
 *             always_use_default_target_path?: bool|Param, // Default: false
 *             default_target_path?: scalar|Param|null, // Default: "/"
 *             target_path_parameter?: scalar|Param|null, // Default: "_target_path"
 *             use_referer?: bool|Param, // Default: false
 *             failure_path?: scalar|Param|null, // Default: null
 *             failure_forward?: bool|Param, // Default: false
 *             failure_path_parameter?: scalar|Param|null, // Default: "_failure_path"
 *             service?: scalar|Param|null, // Default: "ldap"
 *             dn_string?: scalar|Param|null, // Default: "{user_identifier}"
 *             query_string?: scalar|Param|null,
 *             search_dn?: scalar|Param|null, // Default: ""
 *             search_password?: scalar|Param|null, // Default: ""
 *             ldap_users_only?: bool|Param, // Only bind users of class "Symfony\Component\Ldap\Security\LdapUser" against the LDAP server, and leave any other user to the regular password checker. // Default: false
 *         },
 *         json_login?: array{
 *             provider?: scalar|Param|null,
 *             remember_me?: bool|Param, // Deprecated: Setting the "security.firewalls..json_login.remember_me.remember_me" configuration option has no effect and is deprecated. It will be removed in Symfony 9.0. // Default: true
 *             success_handler?: scalar|Param|null,
 *             failure_handler?: scalar|Param|null,
 *             check_path?: scalar|Param|null, // Default: "/login_check"
 *             use_forward?: bool|Param, // Default: false
 *             login_path?: scalar|Param|null, // Default: "/login"
 *             username_path?: scalar|Param|null, // Default: "username"
 *             password_path?: scalar|Param|null, // Default: "password"
 *         },
 *         json_login_ldap?: array{
 *             provider?: scalar|Param|null,
 *             remember_me?: bool|Param, // Deprecated: Setting the "security.firewalls..json_login_ldap.remember_me.remember_me" configuration option has no effect and is deprecated. It will be removed in Symfony 9.0. // Default: true
 *             success_handler?: scalar|Param|null,
 *             failure_handler?: scalar|Param|null,
 *             check_path?: scalar|Param|null, // Default: "/login_check"
 *             use_forward?: bool|Param, // Default: false
 *             login_path?: scalar|Param|null, // Default: "/login"
 *             username_path?: scalar|Param|null, // Default: "username"
 *             password_path?: scalar|Param|null, // Default: "password"
 *             service?: scalar|Param|null, // Default: "ldap"
 *             dn_string?: scalar|Param|null, // Default: "{user_identifier}"
 *             query_string?: scalar|Param|null,
 *             search_dn?: scalar|Param|null, // Default: ""
 *             search_password?: scalar|Param|null, // Default: ""
 *             ldap_users_only?: bool|Param, // Only bind users of class "Symfony\Component\Ldap\Security\LdapUser" against the LDAP server, and leave any other user to the regular password checker. // Default: false
 *         },
 *         access_token?: array{
 *             provider?: scalar|Param|null,
 *             remember_me?: bool|Param, // Deprecated: Setting the "security.firewalls..access_token.remember_me.remember_me" configuration option has no effect and is deprecated. It will be removed in Symfony 9.0. // Default: true
 *             success_handler?: scalar|Param|null,
 *             failure_handler?: scalar|Param|null,
 *             realm?: scalar|Param|null, // Default: null
 *             token_extractors?: Param|string|list<scalar|Param|null>,
 *             token_handler?: Param|string|array{
 *                 id?: scalar|Param|null,
 *                 oidc_user_info?: Param|string|array{
 *                     base_uri?: scalar|Param|null, // Base URI of the userinfo endpoint on the OIDC server, or the OIDC server URI to use the discovery (require "discovery" to be configured). The latter must end with a slash when it has a path, as the discovery path is resolved against it as a relative URL.
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         cache?: array{
 *                             id?: scalar|Param|null, // Cache service id to use to cache the OIDC discovery configuration. // Default: "cache.security.discovery"
 *                         },
 *                     },
 *                     claim?: scalar|Param|null, // Claim which contains the user identifier (e.g. sub, email, etc.). // Default: "sub"
 *                     client?: scalar|Param|null, // HttpClient service id to use to call the OIDC server.
 *                 },
 *                 oidc?: array{
 *                     discovery?: array{ // Enable the OIDC discovery.
 *                         base_uri?: Param|string|list<scalar|Param|null>,
 *                         cache?: array{
 *                             id?: scalar|Param|null, // Cache service id to use to cache the OIDC discovery configuration. // Default: "cache.security.discovery"
 *                         },
 *                         enforce_key_usage_verification?: bool|Param, // When enabled (default), only keys explicitly designated for signature (via "use":"sig" or a "key_ops" entry containing "sign"/"verify") are accepted. When disabled, keys without any usage designation are also accepted; keys explicitly restricted to encryption are still rejected. // Default: true
 *                         check_issuer?: bool|null|array<string, string|Param>,
 *                     },
 *                     claim?: scalar|Param|null, // Claim which contains the user identifier (e.g.: sub, email..). // Default: "sub"
 *                     audience?: Param|string|list<scalar|Param|null>,
 *                     issuers?: list<scalar|Param|null>,
 *                     algorithms?: list<scalar|Param|null>,
 *                     keyset?: scalar|Param|null, // JSON-encoded JWKSet used to sign the token (must contain a list of valid public keys).
 *                     encryption?: bool|array{
 *                         enabled?: bool|Param, // Default: false
 *                         enforce?: bool|Param, // When enabled, the token shall be encrypted. // Default: false
 *                         algorithms?: list<scalar|Param|null>,
 *                         keyset?: scalar|Param|null, // JSON-encoded JWKSet used to decrypt the token (must contain a list of valid private keys).
 *                     },
 *                     allowed_time_drift?: int|Param, // Allowed time drift in seconds for token validation (iat, nbf, exp claims). // Default: 0
 *                     enforce_at_jwt_type?: bool|Param|null, // When enabled, the "typ" header of the token must be "at+jwt" or "application/at+jwt", as RFC 9068 requires from a JWT access token. This rejects the ID tokens issued for the same audience. Disable it only for providers that do not follow the profile. Defaults to false in 8.2 and to true as of 9.0. // Default: null
 *                 },
 *                 cas?: array{
 *                     validation_url?: scalar|Param|null, // CAS server validation URL
 *                     prefix?: scalar|Param|null, // CAS prefix // Default: "cas"
 *                     http_client?: scalar|Param|null, // HTTP Client service // Default: null
 *                 },
 *                 oauth2?: Param|string|array{
 *                     http_client?: scalar|Param|null, // HttpClient service id the introspection endpoint is called with. Declare it as a scoped client whose "base_uri" is the introspection endpoint of your authorization server and whose "auth_basic" holds the credentials it authenticates with. Those are sent as given, where the "client_secret_basic" of RFC 6749 §2.3.1 form-urlencodes both halves, so encode a client id or a secret holding a colon, a plus or a space yourself.
 *                     audience?: Param|string|list<scalar|Param|null>,
 *                     issuer?: scalar|Param|null, // Identifier of the authorization server, checked against the "iss" of the introspection response. // Default: null
 *                     claim?: scalar|Param|null, // Claim which contains the user identifier (e.g.: sub, username, email...). Defaults to "sub", falling back to "username". // Default: null
 *                     allowed_time_drift?: int|Param, // Allowed time drift in seconds when validating the "iat", "nbf" and "exp" of the introspection response. // Default: 0
 *                     cache?: array{ // Cache the introspection responses of active tokens, never beyond their "exp".
 *                         id?: scalar|Param|null, // Cache service id to use to cache the introspection responses.
 *                         ttl?: int|Param, // Maximum lifetime in seconds of a cached introspection response. The shorter it is, the sooner a revoked token stops being accepted. // Default: 60
 *                     },
 *                     response_signature?: bool|array{ // Ask the authorization server for a signed introspection response (RFC 9701) and verify it.
 *                         enabled?: bool|Param, // Default: false
 *                         enforce?: bool|Param, // When enabled (default), a plain JSON introspection response is refused. // Default: true
 *                         algorithms?: list<scalar|Param|null>,
 *                         discovery?: bool|array{ // Read the keys the introspection response is verified against from the RFC 8414 metadata of the authorization server, whose URL is derived from the "issuer" this handler already declares. Only the "jwks_uri" is read from it: which algorithms are accepted stays declared here, so that an authorization server cannot widen it by announcing more.
 *                             enabled?: bool|Param, // Default: false
 *                             cache?: array{
 *                                 id?: scalar|Param|null, // Cache service id the metadata document and the keys it points at are stored in. // Default: "cache.security.discovery"
 *                             },
 *                         },
 *                         keyset?: scalar|Param|null, // JSON-encoded JWKSet holding the public keys of your authorization server, the ones it announces at its "jwks_uri", which the introspection response is verified against. // Default: null
 *                     },
 *                 },
 *             },
 *             resource_metadata?: array{ // Declaring this node serves the RFC 9728 protected resource metadata document of the firewall at "/.well-known/oauth-protected-resource" and advertises its URL in the "resource_metadata" parameter of the "WWW-Authenticate" header, which is how a client discovers where to get a token this firewall accepts. The route is declared by the "security.route_loader" service, which the application must import. Make sure the route is reachable without a token.
 *                 resource?: scalar|Param|null, // The resource identifier of this firewall: an HTTPS URL without a fragment (e.g. "https://example.com/api"), read when the container is compiled, or a path (e.g. "/api") resolved against the origin of the request. Its path is inserted after the well-known path, as RFC 9728, Section 3.1 prescribes. Defaults to the origin of the request, which is what a firewall covering a whole application wants. // Default: null
 *                 authorization_servers?: Param|string|list<scalar|Param|null>,
 *                 jwks_uri?: scalar|Param|null, // URL of the JWK Set holding the keys this resource signs its own responses with. Unrelated to the keys the access tokens are verified against, which belong to the authorization server. // Default: null
 *                 scopes_supported?: Param|string|list<scalar|Param|null>,
 *                 bearer_methods_supported?: Param|string|list<"header"|"body"|"query"|Param>,
 *                 resource_name?: scalar|Param|null, // Human-readable name of this resource, meant to be displayed to the end user. // Default: null
 *                 resource_documentation?: scalar|Param|null, // URL of the developer documentation of this resource. // Default: null
 *                 resource_policy_uri?: scalar|Param|null, // URL of the policy telling how the client may use the data this resource exposes. // Default: null
 *                 resource_tos_uri?: scalar|Param|null, // URL of the terms of service of this resource. // Default: null
 *             },
 *             dpop?: bool|array{ // Accepts only the access tokens bound to a key the request proves it holds (DPoP), presented under the "DPoP" scheme.
 *                 enabled?: bool|Param, // Default: false
 *                 algorithms?: list<scalar|Param|null>,
 *                 cache?: scalar|Param|null, // The cache pool proofs are remembered in to refuse a replay, shared by all the instances of the application. // Default: "cache.security"
 *                 proof_lifetime?: int|Param, // How long a proof is accepted after its "iat", in seconds. // Default: 60
 *                 allowed_time_drift?: int|Param, // Allowed time drift in seconds for the "iat" of a proof, both ways. // Default: 5
 *             },
 *         },
 *         http_basic?: array{
 *             provider?: scalar|Param|null,
 *             realm?: scalar|Param|null, // Default: "Secured Area"
 *         },
 *         http_basic_ldap?: array{
 *             provider?: scalar|Param|null,
 *             realm?: scalar|Param|null, // Default: "Secured Area"
 *             service?: scalar|Param|null, // Default: "ldap"
 *             dn_string?: scalar|Param|null, // Default: "{user_identifier}"
 *             query_string?: scalar|Param|null,
 *             search_dn?: scalar|Param|null, // Default: ""
 *             search_password?: scalar|Param|null, // Default: ""
 *             ldap_users_only?: bool|Param, // Only bind users of class "Symfony\Component\Ldap\Security\LdapUser" against the LDAP server, and leave any other user to the regular password checker. // Default: false
 *         },
 *         remember_me?: array{
 *             secret?: Param|string|list<scalar|Param|null>,
 *             service?: scalar|Param|null,
 *             user_providers?: Param|string|list<scalar|Param|null>,
 *             catch_exceptions?: bool|Param, // Default: true
 *             signature_properties?: list<scalar|Param|null>,
 *             token_provider?: Param|string|array{
 *                 service?: scalar|Param|null, // The service ID of a custom remember-me token provider.
 *                 doctrine?: bool|array{
 *                     enabled?: bool|Param, // Default: false
 *                     connection?: scalar|Param|null, // Default: null
 *                 },
 *             },
 *             token_verifier?: scalar|Param|null, // The service ID of a custom rememberme token verifier.
 *             name?: scalar|Param|null, // Default: "REMEMBERME"
 *             lifetime?: int|Param, // Default: 31536000
 *             path?: scalar|Param|null, // Default: "/"
 *             domain?: scalar|Param|null, // Default: null
 *             secure?: true|false|"auto"|Param, // Defaults to the value of "framework.session.cookie_secure", or to "auto".
 *             httponly?: bool|Param, // Default: true
 *             samesite?: null|"lax"|"strict"|"none"|Param, // Defaults to the value of "framework.session.cookie_samesite", or to "lax".
 *             always_remember_me?: bool|Param, // Default: false
 *             remember_me_parameter?: scalar|Param|null, // Default: "_remember_me"
 *         },
 *     }>,
 *     access_control?: list<array{ // Default: []
 *         request_matcher?: scalar|Param|null, // Default: null
 *         requires_channel?: scalar|Param|null, // Default: null
 *         path?: scalar|Param|null, // Use the urldecoded format. // Default: null
 *         host?: scalar|Param|null, // Default: null
 *         port?: int|Param, // Default: null
 *         ips?: Param|string|list<scalar|Param|null>,
 *         attributes?: array<string, scalar|Param|null>,
 *         route?: scalar|Param|null, // Default: null
 *         methods?: Param|string|list<scalar|Param|null>,
 *         allow_if?: scalar|Param|null, // Default: null
 *         roles?: Param|string|list<scalar|Param|null>,
 *     }>,
 *     role_hierarchy?: array<string, Param|string|list<scalar|Param|null>>,
 * }
 * @psalm-type AiConfig = array{
 *     platform?: array{
 *         albert?: array{
 *             api_key?: string|Param,
 *             base_url?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         amazeeai?: array{
 *             base_url?: string|Param,
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         anthropic?: array{
 *             api_key?: string|Param,
 *             version?: string|Param, // Default: null
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             cache_retention?: "none"|"short"|"long"|Param, // Prompt cache retention policy for Anthropic models // Default: "short"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         azure?: array<string, array{ // Default: []
 *             api_key?: string|Param,
 *             base_url?: string|Param,
 *             deployment?: string|Param,
 *             api_version?: string|Param, // The used API version
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         }>,
 *         bedrock?: array<string, array{ // Default: []
 *             api?: "invoke_model"|"completions"|"responses"|"messages"|Param, // Inference engine and protocol: the SDK-based InvokeModel API, or one of the Bedrock Mantle routes // Default: "invoke_model"
 *             bedrock_runtime_client?: string|Param, // Service ID of the Bedrock runtime client to use; only valid with "api: invoke_model" // Default: null
 *             api_key?: string|Param, // Bedrock API key; when omitted, requests are signed with AWS SigV4. Mantle only
 *             region?: string|Param, // AWS region the Mantle base URL is derived from, defaults to "us-west-2". Mantle only
 *             credential_provider?: string|Param, // Service ID of the AsyncAws credential provider used for SigV4 signing. Mantle only
 *             http_client?: string|Param, // Service ID of the HTTP client to use, defaults to "http_client". Mantle only
 *             path?: string|Param, // Overrides the Mantle request path, for models served on the other path prefix
 *             cache_retention?: "none"|"short"|"long"|Param, // Anthropic Messages prompt-cache retention, defaults to "short"; only valid with "api: messages"
 *             workspace?: string|Param, // Bedrock Mantle workspace ID sent with Anthropic Messages requests; only valid with "api: messages"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         }>,
 *         cache?: array<string, array{ // Default: []
 *             platform?: string|Param,
 *             service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *             cache_key?: string|Param, // Key used to store platform results, if not set, the current platform name will be used, the "prompt_cache_key" can be set during platform call to override this value
 *             ttl?: int|Param,
 *         }>,
 *         cartesia?: array{
 *             api_key?: string|Param,
 *             version?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         cerebras?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         cohere?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         decart?: array{
 *             api_key?: string|Param,
 *             host?: string|Param, // Default: "https://api.decart.ai/v1"
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         deepgram?: array{
 *             api_key?: string|Param,
 *             endpoint?: string|Param, // Deepgram REST API endpoint // Default: "https://api.deepgram.com/v1/"
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         deepseek?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         dockermodelrunner?: array{
 *             host_url?: string|Param, // Default: "http://127.0.0.1:12434"
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         edenai?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 api?: bool|Param, // Fetch the models from the provider API instead of using the bundled list // Default: false
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         elevenlabs?: array{
 *             api_key?: string|Param,
 *             endpoint?: string|Param, // Default: "https://api.elevenlabs.io/v1/"
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         failover?: array<string, array{ // Default: []
 *             platforms?: list<scalar|Param|null>,
 *             rate_limiter?: string|Param,
 *         }>,
 *         fireworks?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         gemini?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         generic?: array<string, array{ // Default: []
 *             base_url?: string|Param,
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *             supports_completions?: bool|Param, // Default: true
 *             supports_embeddings?: bool|Param, // Default: true
 *             completions_path?: string|Param, // Default: "/v1/chat/completions"
 *             embeddings_path?: string|Param, // Default: "/v1/embeddings"
 *         }>,
 *         higgsfield?: array{
 *             api_key?: string|Param,
 *             api_secret?: string|Param,
 *             base_url?: string|Param, // Base URL of the Higgsfield API. Defaults to "https://platform.higgsfield.ai" when null.
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         huggingface?: array{
 *             api_key?: string|Param,
 *             provider?: string|Param, // Inference provider for chat completion, Hugging Face picks one when null // Default: null
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 api?: bool|Param, // Fetch the models from the provider API instead of using the bundled list // Default: false
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         lmstudio?: array{
 *             host_url?: string|Param, // Default: "http://127.0.0.1:1234"
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         minimax?: array{
 *             endpoint?: string|Param, // Default: "https://api.minimax.io/v1"
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         mistral?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         ollama?: array{
 *             endpoint?: string|Param, // Endpoint for Ollama (e.g. "http://127.0.0.1:11434" for local, or a cloud endpoint). If null, the http_client is used as-is and must already be configured with a base URI.
 *             api_key?: string|Param, // API key for Ollama Cloud authentication (optional for local usage)
 *             http_client?: string|Param, // Service ID of the HTTP client to use. When "endpoint" is null, this client must be pre-configured (e.g. with a base_uri). // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         openai?: array{
 *             api_key?: string|Param,
 *             region?: scalar|Param|null, // The region for OpenAI API (EU, US, or null for default) // Default: null
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         openresponses?: array<string, array{ // Default: []
 *             base_url?: string|Param,
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *             responses_path?: string|Param, // Default: "/v1/responses"
 *         }>,
 *         openrouter?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 api?: bool|Param, // Fetch the models from the provider API instead of using the bundled list // Default: false
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         ovh?: array{
 *             api_key?: scalar|Param|null,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         perplexity?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         scaleway?: array{
 *             api_key?: scalar|Param|null,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         together?: array{
 *             api_key?: string|Param,
 *             endpoint?: string|Param, // Base endpoint for the Together API. Defaults to "https://api.together.xyz" when null.
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         transformersphp?: array{
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         typesafe?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         venice?: array{
 *             api_key?: string|Param,
 *             endpoint?: string|Param, // Default: "https://api.venice.ai/api/v1/"
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         vertexai?: array{
 *             location?: string|Param, // Required for the project-scoped endpoint. Must be set together with "project_id". // Default: null
 *             project_id?: string|Param, // Required for the project-scoped endpoint. Must be set together with "location". // Default: null
 *             api_key?: string|Param, // When set without "location" and "project_id", uses the global endpoint. Note: API keys only identify the project for billing and do not provide identity-based access control. For production use with IAM, audit logging, or data residency, prefer the project-scoped endpoint with service account authentication. // Default: null
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *         voyage?: array{
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Service ID of the HTTP client to use // Default: "http_client"
 *             model_catalog?: Param|string|array{ // Model catalog of the platform, a string is short for the "service" option
 *                 service?: string|Param, // Service ID of a custom model catalog to use instead of the bridge one
 *                 models?: array<string, array{ // Default: []
 *                     class?: string|Param, // The fully qualified class name of the model (must extend Symfony\AI\Platform\Model) // Default: "Symfony\\AI\\Platform\\Model"
 *                     capabilities?: list<value-of<\Symfony\AI\Platform\Capability>|\Symfony\AI\Platform\Capability|Param>,
 *                 }>,
 *                 cache?: bool|array{ // Caches the model lookups, "true" is short for enabling it with the defaults
 *                     enabled?: bool|Param, // Default: false
 *                     service?: string|Param, // The cache service id as defined under the "cache" configuration key // Default: "cache.app"
 *                     ttl?: int|Param, // Lifetime of the cached lookups in seconds // Default: 3600
 *                 },
 *             },
 *         },
 *     },
 *     agent?: array<string, array{ // Default: []
 *         platform?: string|Param, // Service name of platform // Default: "Symfony\\AI\\Platform\\PlatformInterface"
 *         model?: mixed,
 *         memory?: mixed, // Memory configuration: string for static memory, or array with "service" key for service reference // Default: null
 *         prompt?: Param|string|array{ // The system prompt configuration
 *             text?: string|Param, // The system prompt text
 *             file?: string|Param, // Path to file containing the system prompt
 *             include_tools?: bool|Param, // Include tool definitions at the end of the system prompt // Default: false
 *             enable_translation?: bool|Param, // Enable translation for the system prompt // Default: false
 *             translation_domain?: string|Param, // The translation domain for the system prompt // Default: null
 *         },
 *         tools?: bool|array{ // Tools are opt-in: set to true to inject all services tagged with "ai.tool", or configure an explicit list of tools. When the option is omitted (or set to null or false), no tools are registered.
 *             enabled?: bool|Param, // Default: false
 *             execution_strategy?: scalar|Param|null, // The tool execution strategy. Built-in options are "sequential" (default) and "fiber". A custom service ID implementing ToolExecutorInterface can also be provided. // Default: null
 *             services?: list<Param|string|array{ // Default: []
 *                 service?: string|Param,
 *                 agent?: string|Param,
 *                 mcp_server?: string|Param, // A remote MCP server whose tools are exposed to this agent, as "<client>.<server>" referencing a connection configured under "mcp.clients".
 *                 name?: string|Param,
 *                 description?: string|Param,
 *                 method?: string|Param,
 *                 prefix?: string|Param, // Only with "mcp_server": prefix put in front of every remote tool name. Defaults to "<server>_".
 *             }>,
 *         },
 *         include_sources?: bool|Param, // Include sources exposed by tools as part of the tool result metadata // Default: false
 *         message_store?: string|Param, // Service name of the message store continuing and saving the conversations of the agent by their ID // Default: null
 *         max_tool_calls?: scalar|Param|null, // Maximum number of tool calls per agent call, null to disable // Default: 50
 *         fault_tolerant_toolbox?: bool|Param, // Continue the agent run even if a tool call fails // Default: true
 *         speech?: bool|array{ // Speech (TTS/STT) decorator configuration
 *             enabled?: bool|Param, // Default: true
 *             text_to_speech_platform?: string|Param, // Service name of the TTS platform (e.g. ai.platform.elevenlabs). // Default: null
 *             speech_to_text_platform?: string|Param, // Service name of the STT platform (e.g. ai.platform.openai). // Default: null
 *             tts_model?: string|Param, // Text-to-speech model name // Default: null
 *             tts_options?: mixed, // Provider-specific TTS options // Default: []
 *             stt_model?: string|Param, // Speech-to-text model name // Default: null
 *             stt_options?: mixed, // Provider-specific STT options // Default: []
 *         },
 *     }>,
 *     multi_agent?: array<string, array{ // Default: []
 *         orchestrator?: string|Param, // Service ID of the orchestrator agent
 *         handoffs?: array<string, list<scalar|Param|null>>,
 *         fallback?: string|Param, // Service ID of the fallback agent for unmatched requests
 *     }>,
 *     store?: array{
 *         azuresearch?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             api_version?: string|Param,
 *             index_name?: string|Param, // The name of the store will be used if the "index_name" option is not set
 *             http_client?: string|Param, // Default: "http_client"
 *             vector_field?: string|Param, // Default: "vector"
 *         }>,
 *         cache?: array<string, array{ // Default: []
 *             service?: string|Param, // Default: "cache.app"
 *             cache_key?: string|Param, // The name of the store will be used if the key is not set.
 *             strategy?: string|Param, // Default: "cosine"
 *         }>,
 *         chromadb?: array<string, array{ // Default: []
 *             client?: string|Param, // Default: "Codewithkyrian\\ChromaDB\\Client"
 *             collection?: string|Param,
 *             embedding_function?: string|Param, // Service id of a Codewithkyrian\ChromaDB\Embeddings\EmbeddingFunction, required to query the store with a TextQuery.
 *         }>,
 *         clickhouse?: array<string, array{ // Default: []
 *             dsn?: string|Param,
 *             http_client?: string|Param,
 *             database?: string|Param,
 *             table?: string|Param,
 *         }>,
 *         cloudflare?: array<string, array{ // Default: []
 *             account_id?: string|Param,
 *             api_key?: string|Param,
 *             index_name?: string|Param,
 *             http_client?: string|Param, // Default: "http_client"
 *             dimensions?: int|Param, // Default: 1536
 *             metric?: string|Param, // Default: "cosine"
 *             endpoint?: string|Param,
 *         }>,
 *         doctrine?: array<string, array{ // Default: []
 *             entity?: string|Param, // FQCN of the entity with #[Embedding] on a field, or on the class when it is mirrored.
 *             metric?: "cosine"|"euclidean"|"inner_product"|Param, // Metric to use for vector similarity search. // Default: "cosine"
 *             entity_manager?: string|Param, // Service id of the entity manager owning the entity. // Default: "doctrine.orm.entity_manager"
 *             vectorizer?: string|Param, // Service id of the vectorizer embedding the entities on flush and through "ai:store:index". // Default: null
 *             mirror_to?: string|Param, // Service id of the store to mirror the entity into, needs a "vectorizer" and #[Embedding] on the entity class. // Default: null
 *         }>,
 *         elasticsearch?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             index_name?: string|Param,
 *             vectors_field?: string|Param, // Default: "_vectors"
 *             dimensions?: int|Param, // Default: 1536
 *             metric?: string|Param, // Default: "cosine"
 *             http_client?: string|Param,
 *         }>,
 *         manticoresearch?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             table?: string|Param,
 *             field?: string|Param, // Default: "_vectors"
 *             type?: string|Param, // Default: "hnsw"
 *             metric?: string|Param, // Default: "cosine"
 *             dimensions?: int|Param, // Default: 1536
 *             quantization?: string|Param,
 *             http_client?: string|Param,
 *         }>,
 *         mariadb?: array<string, array{ // Default: []
 *             connection?: string|Param,
 *             table_name?: string|Param,
 *             index_name?: string|Param,
 *             vector_field_name?: string|Param,
 *             dimensions?: int|Param, // Number of dimensions of the vector column created by "ai:store:setup"
 *             metric?: "cosine"|"euclidean"|"distance"|Param, // Distance metric to use for vector similarity search // Default: "euclidean"
 *         }>,
 *         meilisearch?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             index_name?: string|Param,
 *             http_client?: string|Param, // Default: "http_client"
 *             embedder?: string|Param, // Default: "default"
 *             vector_field?: string|Param, // Default: "_vectors"
 *             dimensions?: int|Param, // Default: 1536
 *             semantic_ratio?: float|Param, // The ratio between semantic (vector) and full-text search (0.0 to 1.0). Default: 1.0 (100% semantic) // Default: 1.0
 *         }>,
 *         memory?: array<string, array{ // Default: []
 *             strategy?: string|Param,
 *         }>,
 *         milvus?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             database?: string|Param,
 *             collection?: string|Param,
 *             vector_field?: string|Param, // Default: "_vectors"
 *             dimensions?: int|Param, // Default: 1536
 *             metric?: string|Param, // Default: "COSINE"
 *             http_client?: string|Param,
 *         }>,
 *         mongodb?: array<string, array{ // Default: []
 *             client?: string|Param, // Default: "MongoDB\\Client"
 *             database?: string|Param,
 *             collection?: string|Param,
 *             index_name?: string|Param,
 *             vector_field?: string|Param, // Default: "vector"
 *             bulk_write?: bool|Param, // Default: false
 *             dimensions?: int|Param, // Number of dimensions of the vector index created by "ai:store:setup"
 *             metric?: "euclidean"|"cosine"|"dotProduct"|Param, // Distance function of the vector index created by "ai:store:setup"
 *             index_fields?: mixed, // Extra fields of the vector index created by "ai:store:setup", like filter fields
 *         }>,
 *         neo4j?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             username?: string|Param,
 *             password?: string|Param,
 *             database?: string|Param,
 *             vector_index_name?: string|Param,
 *             node_name?: string|Param,
 *             vector_field?: string|Param, // Default: "embeddings"
 *             dimensions?: int|Param, // Default: 1536
 *             metric?: string|Param, // Default: "cosine"
 *             quantization?: bool|Param,
 *             http_client?: string|Param,
 *         }>,
 *         opensearch?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             index_name?: string|Param,
 *             vectors_field?: string|Param, // Default: "_vectors"
 *             dimensions?: int|Param, // Default: 1536
 *             metric?: string|Param, // Default: "l2"
 *             http_client?: string|Param,
 *         }>,
 *         pinecone?: array<string, array{ // Default: []
 *             client?: string|Param, // Default: "Probots\\Pinecone\\Client"
 *             index_name?: string|Param,
 *             namespace?: string|Param,
 *             filter?: list<scalar|Param|null>,
 *             top_k?: int|Param,
 *             dimensions?: int|Param, // Number of dimensions of the index created by "ai:store:setup"
 *             metric?: "cosine"|"euclidean"|"dotproduct"|Param, // Distance metric of the index created by "ai:store:setup"
 *             cloud?: string|Param, // Cloud of the serverless index created by "ai:store:setup"
 *             region?: string|Param, // Region of the serverless index created by "ai:store:setup"
 *         }>,
 *         postgres?: array<string, array{ // Default: []
 *             dsn?: string|Param,
 *             username?: string|Param,
 *             password?: string|Param,
 *             table_name?: string|Param,
 *             vector_field?: string|Param, // Default: "embedding"
 *             metric?: "cosine"|"inner_product"|"l1"|"l2"|Param, // Distance metric to use for vector similarity search // Default: "l2"
 *             lang?: string|Param, // Default: "english"
 *             dbal_connection?: string|Param,
 *             dimensions?: int|Param, // Number of dimensions of the vector column created by "ai:store:setup"
 *             vector_type?: "vector"|"halfvec"|Param, // Type of the vector column created by "ai:store:setup"
 *             index_method?: string|Param, // The pgvector index method, "hnsw" by default.
 *             index_opclass?: string|Param, // The operator class of the index, the one of the distance by default (e.g. "vector_l2_ops").
 *         }>,
 *         qdrant?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             collection_name?: string|Param, // The name of the store will be used if the "collection_name" is not set
 *             http_client?: string|Param, // Default: "http_client"
 *             dimensions?: int|Param, // Default: 1536
 *             metric?: string|Param, // Default: "Cosine"
 *             async?: bool|Param, // Default: false
 *         }>,
 *         redis?: array<string, array{ // Default: []
 *             connection_parameters?: mixed, // see https://github.com/phpredis/phpredis?tab=readme-ov-file#example-1
 *             client?: string|Param, // a service id of a Redis client
 *             index_name?: string|Param,
 *             key_prefix?: string|Param, // Default: "vector:"
 *             metric?: "COSINE"|"L2"|"IP"|Param, // Distance metric to use for vector similarity search // Default: "COSINE"
 *             dimensions?: int|Param, // Number of dimensions of the index created by "ai:store:setup"
 *         }>,
 *         s3vectors?: array<string, array{ // Default: []
 *             client?: string|Param, // Service reference to an existing S3VectorsClient
 *             configuration?: list<scalar|Param|null>,
 *             vector_bucket_name?: string|Param,
 *             index_name?: string|Param,
 *             filter?: list<mixed>,
 *             dimensions?: int|Param, // Number of dimensions of the index created by "ai:store:setup"
 *             metric?: "cosine"|"euclidean"|Param, // Distance metric of the index created by "ai:store:setup"
 *             top_k?: int|Param, // Default number of results to return // Default: 3
 *         }>,
 *         sqlite?: array<string, array{ // Default: []
 *             dsn?: string|Param,
 *             connection?: string|Param,
 *             table_name?: string|Param,
 *             strategy?: string|Param,
 *             vec?: bool|Param, // Default: false
 *             metric?: "cosine"|"L2"|Param, // Default: "cosine"
 *             dimensions?: int|Param, // Default: 1536
 *         }>,
 *         supabase?: array<string, array{ // Default: []
 *             http_client?: string|Param, // Service ID of the HTTP client to use
 *             url?: string|Param,
 *             api_key?: string|Param,
 *             table?: string|Param,
 *             vector_field?: string|Param, // Default: "embedding"
 *             dimensions?: int|Param, // Default: 1536
 *             function_name?: string|Param, // Default: "match_documents"
 *         }>,
 *         surrealdb?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             username?: string|Param,
 *             password?: string|Param,
 *             namespace?: string|Param,
 *             database?: string|Param,
 *             table?: string|Param,
 *             http_client?: string|Param, // Default: "http_client"
 *             vector_field?: string|Param, // Default: "_vectors"
 *             metric?: string|Param, // Default: "cosine"
 *             dimensions?: int|Param, // Default: 1536
 *             namespaced_user?: bool|Param,
 *         }>,
 *         typesense?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             collection?: string|Param,
 *             http_client?: string|Param, // Default: "http_client"
 *             vector_field?: string|Param, // Default: "_vectors"
 *             dimensions?: int|Param, // Default: 1536
 *         }>,
 *         weaviate?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             http_client?: string|Param, // Default: "http_client"
 *             collection?: string|Param, // The name of the store will be used if the "collection" is not set
 *         }>,
 *         vektor?: array<string, array{ // Default: []
 *             storage_path?: string|Param, // Default: "%kernel.project_dir%/var/share"
 *             dimensions?: int|Param, // Default: 1536
 *         }>,
 *     },
 *     message_store?: array{
 *         cache?: array<string, array{ // Default: []
 *             service?: string|Param, // Default: "cache.app"
 *             key?: string|Param, // The name of the message store will be used if the key is not set
 *             ttl?: int|Param,
 *         }>,
 *         cloudflare?: array<string, array{ // Default: []
 *             account_id?: string|Param,
 *             api_key?: string|Param,
 *             namespace?: string|Param,
 *             endpoint_url?: string|Param, // If the version of the Cloudflare API is updated, use this key to support it.
 *         }>,
 *         doctrine?: array{
 *             dbal?: array<string, array{ // Default: []
 *                 connection?: string|Param,
 *                 table_name?: string|Param, // The name of the message store will be used if the table_name is not set
 *             }>,
 *         },
 *         meilisearch?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             api_key?: string|Param,
 *             index_name?: string|Param,
 *         }>,
 *         memory?: array<string, array{ // Default: []
 *         }>,
 *         mongodb?: array<string, array{ // Default: []
 *             client?: string|Param, // Default: "MongoDB\\Client"
 *             database?: string|Param,
 *             collection?: string|Param,
 *         }>,
 *         pogocache?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             password?: string|Param,
 *             key?: string|Param,
 *         }>,
 *         redis?: array<string, array{ // Default: []
 *             connection_parameters?: mixed, // see https://github.com/phpredis/phpredis?tab=readme-ov-file#example-1
 *             client?: string|Param, // a service id of a Redis client
 *             endpoint?: string|Param,
 *             index_name?: string|Param,
 *         }>,
 *         session?: array<string, array{ // Default: []
 *             identifier?: string|Param,
 *         }>,
 *         surrealdb?: array<string, array{ // Default: []
 *             endpoint?: string|Param,
 *             username?: string|Param,
 *             password?: string|Param,
 *             namespace?: string|Param,
 *             database?: string|Param,
 *             table?: string|Param,
 *             namespaced_user?: bool|Param, // Using a namespaced user is a good practice to prevent any undesired access to a specific table, see https://surrealdb.com/docs/surrealdb/reference-guide/security-best-practices
 *         }>,
 *     },
 *     vectorizer?: array<string, array{ // Default: []
 *         platform?: string|Param, // Service name of platform // Default: "Symfony\\AI\\Platform\\PlatformInterface"
 *         model?: mixed,
 *     }>,
 *     indexer?: array<string, array{ // Default: []
 *         loader?: string|Param, // Service name of loader // Default: null
 *         source?: mixed, // Source identifier (file path, URL, etc.) or array of sources // Default: null
 *         transformers?: list<scalar|Param|null>,
 *         filters?: list<scalar|Param|null>,
 *         vectorizer?: scalar|Param|null, // Service name of vectorizer // Default: "Symfony\\AI\\Store\\Document\\VectorizerInterface"
 *         store?: string|Param, // Service name of store // Default: "Symfony\\AI\\Store\\StoreInterface"
 *     }>,
 *     retriever?: array<string, array{ // Default: []
 *         vectorizer?: scalar|Param|null, // Service name of vectorizer // Default: "Symfony\\AI\\Store\\Document\\VectorizerInterface"
 *         store?: string|Param, // Service name of store // Default: "Symfony\\AI\\Store\\StoreInterface"
 *     }>,
 * }
 * @psalm-type SurvosKitConfig = array{
 *     webhook?: array{
 *         http_client?: scalar|Param|null, // Default: null
 *         transports?: list<scalar|Param|null>,
 *     },
 * }
 * @psalm-type SurvosImagorConfig = array{
 *     host?: scalar|Param|null, // Base URL of the imagor server, e.g. https://imagor.survos.com // Default: "%env(default::IMAGOR_HOST)%"
 *     secret?: scalar|Param|null, // IMAGOR_SECRET on the server; signs every URL // Default: "%env(default::IMAGOR_SECRET)%"
 *     signer_truncate?: int|Param, // IMAGOR_SIGNER_TRUNCATE on the server (signature is HMAC-SHA256, base64url) // Default: 40
 *     buckets?: list<scalar|Param|null>,
 *     s3_endpoint?: scalar|Param|null, // Our object storage; https URLs into it are rewritten to {bucket}/{key} (read in place, never copied) // Default: "%env(default::S3_ENDPOINT)%"
 *     source_bucket?: scalar|Param|null, // S3_STORAGE_BUCKET on the server: where cached external originals live // Default: "museado"
 *     source_prefix?: scalar|Param|null, // S3_STORAGE_BASE_DIR on the server // Default: "imagor/src"
 *     presets?: array<string, scalar|Param|null>,
 * }
 * @psalm-type WebProfilerConfig = array{
 *     toolbar?: bool|array{ // Profiler toolbar configuration
 *         enabled?: bool|Param, // Default: false
 *         ajax_replace?: bool|Param, // Replace toolbar on AJAX requests // Default: false
 *     },
 *     intercept_redirects?: bool|Param, // Default: false
 *     excluded_ajax_paths?: scalar|Param|null, // Default: "^/((index|app(_[\\w]+)?)\\.php/)?_wdt"
 * }
 * @psalm-type UxRouterConfig = array{
 *     dump_directory?: scalar|Param|null, // The directory where routes and TypeScript types are dumped. // Default: "%kernel.project_dir%/var/routes"
 *     dump_typescript?: bool|Param, // Control whether TypeScript types are dumped alongside routes. Disable this if you do not use TypeScript (e.g. in production when using AssetMapper). // Default: true
 *     routes?: Param|string|list<scalar|Param|null>,
 * }
 * @psalm-type StimulusConfig = array{
 *     controller_paths?: list<scalar|Param|null>,
 *     controllers_json?: scalar|Param|null, // Default: "%kernel.project_dir%/assets/controllers.json"
 * }
 * @psalm-type LiveComponentConfig = array{
 *     secret?: scalar|Param|null, // The secret used to compute fingerprints and checksums // Default: "%kernel.secret%"
 *     fetch_credentials?: "same-origin"|"include"|"omit"|Param, // The default fetch credentials mode for all Live Components ('same-origin', 'include', 'omit') // Default: "same-origin"
 * }
 * @psalm-type UxIconsConfig = array{
 *     icon_dir?: scalar|Param|null, // The local directory where icons are stored. // Default: "%kernel.project_dir%/assets/icons"
 *     default_icon_attributes?: array<string, scalar|Param|null>,
 *     icon_sets?: array<string, array{ // the icon set prefix (e.g. "acme") // Default: []
 *         path?: scalar|Param|null, // The local icon set directory path. (cannot be used with 'alias')
 *         alias?: scalar|Param|null, // The remote icon set identifier. (cannot be used with 'path')
 *         icon_attributes?: array<string, scalar|Param|null>,
 *         suffixes?: array<string, array{ // The suffix name (e.g. "solid", "20-solid") // Default: []
 *             icon_attributes?: array<string, scalar|Param|null>,
 *         }>,
 *     }>,
 *     aliases?: array<string, string|Param>,
 *     iconify?: bool|array{ // Configuration for the remote icon service.
 *         enabled?: bool|Param, // Default: true
 *         on_demand?: bool|Param, // Whether to download icons "on demand". // Default: true
 *         auto_lock?: bool|Param, // Persist "on demand" icons to the local icon directory (see "icon_dir"). Recommended in dev only. Requires "on_demand" to be enabled. // Default: false
 *         endpoint?: scalar|Param|null, // The endpoint for the Iconify icons API. // Default: "https://api.iconify.design"
 *     },
 *     ignore_not_found?: bool|Param, // Ignore error when an icon is not found. Set to 'true' to fail silently. // Default: false
 * }
 * @psalm-type SurvosFieldConfig = array{
 *     routes_enabled?: bool|Param, // Set false to manage this bundle's routes manually in your app. Bundles exposing sensitive routes (e.g. running console commands) should default this off. // Default: true
 *     route_prefix?: scalar|Param|null, // URL prefix applied to all routes from this bundle. // Default: ""
 *     locale_prefix?: bool|Param, // Prepend {_locale} (constrained to kernel.enabled_locales) to this bundle's route prefix, e.g. /{_locale}/f instead of /f -- for bundles whose routes are meant to be shared/bookmarked, so the URL itself carries the locale instead of a query param. // Default: false
 * }
 * @psalm-type SurvosSearchConfig = array{
 *     routes_enabled?: bool|Param, // Set false to manage this bundle's routes manually in your app. Bundles exposing sensitive routes (e.g. running console commands) should default this off. // Default: true
 *     route_prefix?: scalar|Param|null, // URL prefix applied to all routes from this bundle. // Default: ""
 *     locale_prefix?: bool|Param, // Prepend {_locale} (constrained to kernel.enabled_locales) to this bundle's route prefix, e.g. /{_locale}/f instead of /f -- for bundles whose routes are meant to be shared/bookmarked, so the URL itself carries the locale instead of a query param. // Default: false
 *     default_adapter?: scalar|Param|null, // Default: "default"
 *     public_searches?: list<scalar|Param|null>,
 *     entity_adapters?: array<string, scalar|Param|null>,
 *     index_prefix?: scalar|Param|null, // Prefix applied to every Elasticsearch index name, once, by ElasticIndexNameResolver. Reuses MEILI_PREFIX so one app has one index namespace across both engines. Leaving it unset is an error the first time a name is resolved: bare index names share a flat cluster namespace with every other app on the node. Set it to an empty string to share deliberately. // Default: "%env(default::MEILI_PREFIX)%"
 *     adapters?: array<string, Param|string|array{ // Default: {"default":{"dsn":"doctrine://default"}}
 *         dsn?: scalar|Param|null,
 *     }>,
 *     default_hits_per_page?: int|Param, // Default: 24
 *     default_hits_per_page_choices?: list<int|Param>,
 * }
 * @psalm-type SurvosJsonlConfig = array{
 *     compression_level?: int|Param, // Default: 1
 * }
 * @psalm-type SurvosElasticConfig = array{
 *     spool_dir?: scalar|Param|null, // Where postFlush writes the ids awaiting reindex. // Default: "%kernel.project_dir%/var/elastic-spool"
 *     spool_enabled?: bool|Param, // Turn the Doctrine listener off for bulk imports that reindex explicitly afterwards. // Default: true
 *     async?: bool|Param, // Dispatch reindex work through Messenger. With this off (or with no bus installed) the listener writes a JSONL spool for elastic:spool:flush instead -- the right mode for bulk imports. // Default: true
 *     batch_size?: int|Param, // Ids per message. One huge flush becomes several bounded jobs. // Default: 500
 *     handler_batch_size?: int|Param, // ReindexDocuments messages the worker collects before reconciling them in one query and bulk request per class. // Default: 50
 *     handler_idle_timeout?: int|Param, // Seconds of worker idleness after which a partial batch is reconciled. 0 waits for a full batch. // Default: 1
 *     analysis?: array{ // Text analysis. Without it every text field uses the "standard" analyzer, which does no stemming and no accent folding -- searches work but are markedly worse, and any comparison against Meilisearch is unfair. index.analysis is a STATIC setting, so changing this needs elastic:index:rebuild.
 *         language?: scalar|Param|null, // Elasticsearch stemmer language: english, hungarian, spanish, german, french, ... Null leaves the default analyzer in place. // Default: null
 *         ascii_folding?: bool|Param, // Fold accents so "Kovacs" matches "Kovács". Applies only when a language is set. // Default: true
 *     },
 *     index_pattern?: scalar|Param|null, // Which cluster indices the admin page considers this app's, e.g. "kpa_*". The cluster index namespace is flat and shared by every app pointed at the node, so this is how the page finds indices this app owns but never declared -- a leftover from a rename, a locale variant. Defaults to survos_search.index_prefix + "*", so it tracks exactly what this app writes; set it only to widen or narrow that deliberately. // Default: null
 *     elasticvue_url?: scalar|Param|null, // Elasticvue (https://elasticvue.com) — the closest equivalent to the riccox Meilisearch UI. Point this at a self-hosted instance (docker run -p 8080:8080 cars10/elasticvue) or https://app.elasticvue.com. Null hides the menu link. Note that Elasticvue talks to Elasticsearch from the browser, so the node needs http.cors.enabled unless it is proxied. // Default: null
 *     kibana_url?: scalar|Param|null, // Browser-facing Kibana base URL (including any space/base path). In debug, null defaults to localhost:5601 only when all ES connections are loopback; otherwise the link is hidden. // Default: null
 *     server_url?: scalar|Param|null, // The Elasticsearch node itself, for a direct link in the admin menu. Null hides it. // Default: null
 *     routes_enabled?: bool|Param, // Set false to manage this bundle's routes manually in your app. Bundles exposing sensitive routes (e.g. running console commands) should default this off. // Default: true
 *     route_prefix?: scalar|Param|null, // URL prefix applied to all routes from this bundle. // Default: "/admin/elastic"
 *     locale_prefix?: bool|Param, // Prepend {_locale} (constrained to kernel.enabled_locales) to this bundle's route prefix, e.g. /{_locale}/f instead of /f -- for bundles whose routes are meant to be shared/bookmarked, so the URL itself carries the locale instead of a query param. // Default: false
 * }
 * @psalm-type SurvosCloudflaredConfig = array{
 *     enabled?: bool|Param|null, // Resolve the tunnel and serve /_cloudflared/whoami. Default: on in the dev environment only, so prod never probes loopback ports. // Default: null
 *     port?: int|Param, // This app's local port. Default: looked up by project directory in the Symfony proxy index, which already knows the port pinned in .symfony.local.yaml. // Default: null
 *     hostname?: scalar|Param|null, // Force the public hostname instead of finding it in the running tunnel's ingress. Rarely right: a pinned name is the hand-maintained value this bundle exists to remove. // Default: null
 *     machine?: scalar|Param|null, // Hostname prefix for this machine (m4 → m4-<app>.<zone>). Per machine, not per app, hence an env var set once in the shell profile. // Default: "%env(default::CLOUDFLARED_MACHINE)%"
 *     zone?: scalar|Param|null, // DNS zone the tunnel hostnames live in: dev-only survos.org (see README "Hostnames"). // Default: "survos.org"
 *     proxy_index?: scalar|Param|null, // Symfony CLI proxy project index. // Default: "http://127.0.0.1:7080/index.json"
 *     metrics?: list<scalar|Param|null>,
 *     guard_debug_routes?: bool|Param, // 404 /_profiler and /_wdt, and drop X-Debug-Token, for requests that came through Cloudflare. // Default: true
 * }
 * @psalm-type ConfigType = array{
 *     imports?: ImportsConfig,
 *     parameters?: ParametersConfig,
 *     services?: ServicesConfig,
 *     router?: RouterConfig,
 *     cache?: CacheConfig,
 *     asset?: AssetConfig,
 *     serializer?: SerializerConfig,
 *     validation?: ValidationConfig,
 *     translation?: TranslationConfig,
 *     lock?: LockConfig,
 *     workflow?: WorkflowConfig,
 *     type_info?: TypeInfoConfig,
 *     property_access?: PropertyAccessConfig,
 *     property_info?: PropertyInfoConfig,
 *     uid?: UidConfig,
 *     asset_mapper?: AssetMapperConfig,
 *     http_client?: HttpClientConfig,
 *     framework?: FrameworkConfig,
 *     doctrine?: DoctrineConfig,
 *     doctrine_migrations?: DoctrineMigrationsConfig,
 *     twig?: TwigConfig,
 *     twig_extra?: TwigExtraConfig,
 *     twig_component?: TwigComponentConfig,
 *     security?: SecurityConfig,
 *     ai?: AiConfig,
 *     survos_kit?: SurvosKitConfig,
 *     survos_imagor?: SurvosImagorConfig,
 *     ux_router?: UxRouterConfig,
 *     stimulus?: StimulusConfig,
 *     live_component?: LiveComponentConfig,
 *     ux_icons?: UxIconsConfig,
 *     survos_field?: SurvosFieldConfig,
 *     survos_search?: SurvosSearchConfig,
 *     survos_jsonl?: SurvosJsonlConfig,
 *     survos_elastic?: SurvosElasticConfig,
 *     survos_cloudflared?: SurvosCloudflaredConfig,
 *     "when@dev"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         router?: RouterConfig,
 *         cache?: CacheConfig,
 *         asset?: AssetConfig,
 *         serializer?: SerializerConfig,
 *         validation?: ValidationConfig,
 *         translation?: TranslationConfig,
 *         lock?: LockConfig,
 *         workflow?: WorkflowConfig,
 *         type_info?: TypeInfoConfig,
 *         property_access?: PropertyAccessConfig,
 *         property_info?: PropertyInfoConfig,
 *         uid?: UidConfig,
 *         asset_mapper?: AssetMapperConfig,
 *         http_client?: HttpClientConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         twig?: TwigConfig,
 *         twig_extra?: TwigExtraConfig,
 *         twig_component?: TwigComponentConfig,
 *         security?: SecurityConfig,
 *         ai?: AiConfig,
 *         survos_kit?: SurvosKitConfig,
 *         survos_imagor?: SurvosImagorConfig,
 *         web_profiler?: WebProfilerConfig,
 *         ux_router?: UxRouterConfig,
 *         stimulus?: StimulusConfig,
 *         live_component?: LiveComponentConfig,
 *         ux_icons?: UxIconsConfig,
 *         survos_field?: SurvosFieldConfig,
 *         survos_search?: SurvosSearchConfig,
 *         survos_jsonl?: SurvosJsonlConfig,
 *         survos_elastic?: SurvosElasticConfig,
 *         survos_cloudflared?: SurvosCloudflaredConfig,
 *     },
 *     "when@prod"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         router?: RouterConfig,
 *         cache?: CacheConfig,
 *         asset?: AssetConfig,
 *         serializer?: SerializerConfig,
 *         validation?: ValidationConfig,
 *         translation?: TranslationConfig,
 *         lock?: LockConfig,
 *         workflow?: WorkflowConfig,
 *         type_info?: TypeInfoConfig,
 *         property_access?: PropertyAccessConfig,
 *         property_info?: PropertyInfoConfig,
 *         uid?: UidConfig,
 *         asset_mapper?: AssetMapperConfig,
 *         http_client?: HttpClientConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         twig?: TwigConfig,
 *         twig_extra?: TwigExtraConfig,
 *         twig_component?: TwigComponentConfig,
 *         security?: SecurityConfig,
 *         ai?: AiConfig,
 *         survos_kit?: SurvosKitConfig,
 *         survos_imagor?: SurvosImagorConfig,
 *         ux_router?: UxRouterConfig,
 *         stimulus?: StimulusConfig,
 *         live_component?: LiveComponentConfig,
 *         ux_icons?: UxIconsConfig,
 *         survos_field?: SurvosFieldConfig,
 *         survos_search?: SurvosSearchConfig,
 *         survos_jsonl?: SurvosJsonlConfig,
 *         survos_elastic?: SurvosElasticConfig,
 *         survos_cloudflared?: SurvosCloudflaredConfig,
 *     },
 *     "when@test"?: array{
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         router?: RouterConfig,
 *         cache?: CacheConfig,
 *         asset?: AssetConfig,
 *         serializer?: SerializerConfig,
 *         validation?: ValidationConfig,
 *         translation?: TranslationConfig,
 *         lock?: LockConfig,
 *         workflow?: WorkflowConfig,
 *         type_info?: TypeInfoConfig,
 *         property_access?: PropertyAccessConfig,
 *         property_info?: PropertyInfoConfig,
 *         uid?: UidConfig,
 *         asset_mapper?: AssetMapperConfig,
 *         http_client?: HttpClientConfig,
 *         framework?: FrameworkConfig,
 *         doctrine?: DoctrineConfig,
 *         doctrine_migrations?: DoctrineMigrationsConfig,
 *         twig?: TwigConfig,
 *         twig_extra?: TwigExtraConfig,
 *         twig_component?: TwigComponentConfig,
 *         security?: SecurityConfig,
 *         ai?: AiConfig,
 *         survos_kit?: SurvosKitConfig,
 *         survos_imagor?: SurvosImagorConfig,
 *         web_profiler?: WebProfilerConfig,
 *         ux_router?: UxRouterConfig,
 *         stimulus?: StimulusConfig,
 *         live_component?: LiveComponentConfig,
 *         ux_icons?: UxIconsConfig,
 *         survos_field?: SurvosFieldConfig,
 *         survos_search?: SurvosSearchConfig,
 *         survos_jsonl?: SurvosJsonlConfig,
 *         survos_elastic?: SurvosElasticConfig,
 *         survos_cloudflared?: SurvosCloudflaredConfig,
 *     },
 *     ...<string, ExtensionType|array{ // extra keys must follow the when@%env% pattern or match an extension alias
 *         imports?: ImportsConfig,
 *         parameters?: ParametersConfig,
 *         services?: ServicesConfig,
 *         ...<string, ExtensionType>,
 *     }>
 * }
 */
final class App
{
    /**
     * @param ConfigType $config
     *
     * @psalm-return ConfigType
     */
    public static function config(array $config): array
    {
        /** @var ConfigType $config */
        $config = AppReference::config($config);

        return $config;
    }
}

namespace Symfony\Component\Routing\Loader\Configurator;

/**
 * This class provides array-shapes for configuring the routes of an application.
 *
 * Example:
 *
 *     ```php
 *     // config/routes.php
 *     namespace Symfony\Component\Routing\Loader\Configurator;
 *
 *     return Routes::config([
 *         'controllers' => [
 *             'resource' => 'routing.controllers',
 *         ],
 *     ]);
 *     ```
 *
 * @psalm-type RouteConfig = array{
 *     path: string|array<string,string>,
 *     controller?: string,
 *     template?: string,
 *     redirect_to_route?: string,
 *     redirect_to?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     add_condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 *     firewall?: string,
 * }
 * @psalm-type ImportConfig = array{
 *     resource: string,
 *     type?: string,
 *     exclude?: string|list<string>,
 *     prefix?: string|array<string,string>,
 *     name_prefix?: string,
 *     trailing_slash_on_root?: bool,
 *     controller?: string,
 *     methods?: string|list<string>,
 *     requirements?: array<string,string>,
 *     defaults?: array<string,mixed>,
 *     options?: array<string,mixed>,
 *     host?: string|array<string,string>,
 *     schemes?: string|list<string>,
 *     condition?: string,
 *     add_condition?: string,
 *     locale?: string,
 *     format?: string,
 *     utf8?: bool,
 *     stateless?: bool,
 *     firewall?: string,
 * }
 * @psalm-type AliasConfig = array{
 *     alias: string,
 *     deprecated?: array{package:string, version:string, message?:string},
 * }
 * @psalm-type RoutesConfig = array{
 *     "when@dev"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     "when@prod"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     "when@test"?: array<string, RouteConfig|ImportConfig|AliasConfig>,
 *     ...<string, RouteConfig|ImportConfig|AliasConfig>
 * }
 */
final class Routes
{
    /**
     * @param RoutesConfig $config
     *
     * @psalm-return RoutesConfig
     */
    public static function config(array $config): array
    {
        return $config;
    }
}
