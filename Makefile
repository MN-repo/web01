config-schema.dhall: config.dhall.sample
	dhall type < config.dhall.sample > config-schema.dhall
