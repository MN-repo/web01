# jmp-register

The main website and web registration stub for JMP.

This is a rackup-compatible Ruby web application designed to be run with an Eventmachine-aware server such as Thin.

Get the `dhall` command from your OS or https://github.com/dhall-lang/dhall-haskell/releases

    make
    bundle install --path=.gems
	 env CONFIG=./config.dhall bundle exec rackup
