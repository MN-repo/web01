(use-modules
  ((guix licenses) #:prefix license:)
  (guix packages)
  (guix download)
  (guix git-download)
  (guix build-system ruby)
  (guix build-system copy)
  (guix utils)
  (gnu packages dhall)
  (gnu packages ruby)
  (gnu packages rails)
  (gnu packages databases)
  (gnu packages tls)
  (gnu packages web)
  (ice-9 rdelim)
  (ice-9 popen))

(define-public ruby-statsd-instrument+graphite
  (package
    (name "ruby-statsd-instrument")
    (version "3.1.2+graphite")
    (source
     (origin
       (method git-fetch)
       (uri (git-reference
             (url "https://github.com/singpolyma/statsd-instrument")
             (commit "graphite")))
       (file-name (git-file-name name version))
       (sha256
        (base32
         "1pj87difhx2vkdxv75q7yskla2wg4g0h2528xsxxd27qa4bscf40"))))
    (build-system ruby-build-system)
    (arguments
     `(#:tests? #f))
    (synopsis
      "A StatsD client for Ruby apps. Provides metaprogramming methods to inject StatsD instrumentation into your code.")
    (description
      "This package provides a StatsD client for Ruby apps.  Provides metaprogramming methods to inject StatsD instrumentation into your code.")
    (home-page "https://github.com/Shopify/statsd-instrument")
    (license license:expat)))

(define-public ruby-eventmachine-openssl
  (package
    (inherit ruby-eventmachine)
    (inputs `(("openssl" ,openssl)))))

(define-public ruby-hiredis
  (package
    (name "ruby-hiredis")
    (version "0.6.3")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "hiredis" version))
        (sha256
          (base32 "04jj8k7lxqxw24sp0jiravigdkgsyrpprxpxm71ba93x1wr2w1bz"))))
    (build-system ruby-build-system)
    (arguments
     `(#:phases
       (modify-phases %standard-phases
         (add-before 'build 'use-cc-for-build
           (lambda _
             (setenv "CC" ,(cc-for-target))
             #t)))))
    (native-inputs
     `(("ruby-rake-compiler" ,ruby-rake-compiler)))
    (synopsis
      "Ruby wrapper for hiredis (protocol serialization/deserialization and blocking I/O)")
    (description
      "Ruby wrapper for hiredis (protocol serialization/deserialization and blocking I/O)")
    (home-page "http://github.com/redis/hiredis-rb")
    (license #f)))

(define-public ruby-em-hiredis
  (package
    (name "ruby-em-hiredis")
    (version "0.3.1")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "em-hiredis" version))
        (sha256
          (base32 "0lh276x6wngq9xy75fzzvciinmdlys93db7chy968i18japghk6z"))))
    (build-system ruby-build-system)
    (arguments
     ;; Require too-old rspec
     `(#:tests? #f))
    (propagated-inputs
      `(("ruby-eventmachine" ,ruby-eventmachine)
        ("ruby-hiredis" ,ruby-hiredis)))
    (synopsis "Eventmachine redis client using hiredis native parser")
    (description "Eventmachine redis client using hiredis native parser")
    (home-page "http://github.com/mloughran/em-hiredis")
    (license #f)))

(define-public ruby-sucker-punch
  (package
    (name "ruby-sucker-punch")
    (version "2.0.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "sucker_punch" version))
        (sha256
          (base32
            "008vv7gpv2nm5n1njzvabd3aagbywc240y23vifvq6plir53ybay"))))
    (build-system ruby-build-system)
    (arguments
     `(#:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'less-strict-dependencies
           (lambda _
             (substitute* "sucker_punch.gemspec"
               (("1.0.0") "1.0"))
             #t)))))
    (propagated-inputs
      `(("ruby-concurrent" ,ruby-concurrent)))
    (native-inputs
     `(("ruby-pry" ,ruby-pry)))
    (synopsis
      "Asynchronous processing library for Ruby")
    (description
      "Asynchronous processing library for Ruby")
    (home-page
      "https://github.com/brandonhilkert/sucker_punch")
    (license license:expat)))

(define-public ruby-niceogiri
  (package
    (name "ruby-niceogiri")
    (version "1.1.2")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "niceogiri" version))
        (sha256
          (base32
            "1ha93211bc9cvh23s9w89zz7rq8irpf64ccd9arvg8v1sxg2798a"))))
    (build-system ruby-build-system)
    (arguments
     `(#:test-target "spec"
       #:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'less-strict-dependencies
           (lambda _
             (substitute* "niceogiri.gemspec"
               (("2.7") "3.8")
               (("1.0") "2.0")
               ((".*guard-rspec.*") "\n"))
             #t)))))
    (propagated-inputs
      `(("ruby-nokogiri" ,ruby-nokogiri)))
    (native-inputs
     `(("ruby-rspec" ,ruby-rspec)
       ("ruby-yard" ,ruby-yard)))
    (synopsis "Make dealing with XML less painful")
    (description
      "Make dealing with XML less painful")
    (home-page
      "https://github.com/benlangfeld/Niceogiri")
    (license license:expat)))

(define-public ruby-countdownlatch
  (package
    (name "ruby-countdownlatch")
    (version "1.0.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "countdownlatch" version))
        (sha256
          (base32
            "1v6pbay6z07fp7yvnba1hmyacbicvmjndd8rn2h1b5rmpcb5s0j3"))))
    (build-system ruby-build-system)
    (synopsis
      "A synchronization aid that allows one or more threads to wait until a set of operations being performed in other threads completes")
    (description
      "This package provides a synchronization aid that allows one or more threads to wait until a set of operations being performed in other threads completes")
    (home-page
      "https://github.com/benlangfeld/countdownlatch")
    (license license:expat)))

(define-public ruby-blather
  (package
    (name "ruby-blather")
    (version "2.0.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "blather" version))
        (sha256
          (base32
            "05ry2x835fj4pzk61282pcz86n018cr39zbgwbi213md74i90s7c"))))
    (build-system ruby-build-system)
    (arguments
     `(#:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'less-strict-dependencies
           (lambda _
             (substitute* "blather.gemspec"
               ((".*guard-rspec.*") "\n")
                ((".*bluecloth.*") "\n")
                ((".*bundler.*") "\n"))
             #t)))))
    (propagated-inputs
      `(("ruby-activesupport" ,ruby-activesupport)
        ("ruby-eventmachine" ,ruby-eventmachine)
        ("ruby-niceogiri" ,ruby-niceogiri)
        ("ruby-nokogiri" ,ruby-nokogiri)
        ("ruby-sucker-punch" ,ruby-sucker-punch)))
    (native-inputs
     `(("ruby-rspec" ,ruby-rspec)
       ("ruby-yard" ,ruby-yard)
       ("ruby-countdownlatch" ,ruby-countdownlatch)
       ("ruby-rb-fsevent" ,ruby-rb-fsevent)
       ("ruby-mocha" ,ruby-mocha)))
    (synopsis
      "An XMPP DSL for Ruby written on top of EventMachine and Nokogiri")
    (description
      "An XMPP DSL for Ruby written on top of EventMachine and Nokogiri")
    (home-page "http://adhearsion.com/blather")
    (license license:expat)))

(define-public ruby-value-semantics
  (package
    (name "ruby-value-semantics")
    (version "3.6.1")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "value_semantics" version))
        (sha256
          (base32
            "1vdwai8wf6r1fkvdpyz1vzxm89q7ghjvb3pqpg2kvwibwzd99dnx"))))
    (build-system ruby-build-system)
    (arguments
     `(#:phases
       (modify-phases %standard-phases
         (replace 'check
           (lambda _
             (invoke "rspec")
             #t)))))
    (native-inputs
     `(("ruby-rspec" ,ruby-rspec)))
    (synopsis
      "
    Generates modules that provide conventional value semantics for a given set of attributes.
    The behaviour is similar to an immutable `Struct` class,
    plus extensible, lightweight validation and coercion.
  ")
    (description
      "
    Generates modules that provide conventional value semantics for a given set of attributes.
    The behaviour is similar to an immutable `Struct` class,
    plus extensible, lightweight validation and coercion.
  ")
    (home-page
      "https://github.com/tomdalling/value_semantics")
    (license license:expat)))

(define-public ruby-promise.rb
  (package
    (name "ruby-promise.rb")
    (version "0.7.4")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "promise.rb" version))
        (sha256
          (base32
            "0a819sikcqvhi8hck1y10d1nv2qkjvmmm553626fmrh51h2i089d"))))
    (build-system ruby-build-system)
    (arguments
     `(#:test-target "spec"
       #:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'less-strict-dependencies
           (lambda _
             (substitute* "Rakefile"
               (("if Gem.ruby_version.*") "if false\n"))
             (substitute* "spec/spec_helper.rb"
               ((".*devtools/spec_helper.*") "\n"))
             #t)))))
    (native-inputs
     `(("ruby-rspec" ,ruby-rspec)
       ("ruby-rspec-its" ,ruby-rspec-its)
       ("ruby-awesome-print" ,ruby-awesome-print)
       ("ruby-fuubar" ,ruby-fuubar)))
    (synopsis "Promises/A+ for Ruby")
    (description "Promises/A+ for Ruby")
    (home-page "https://github.com/lgierth/promise")
    (license license:unlicense)))

(define-public ruby-multicodecs
  (package
    (name "ruby-multicodecs")
    (version "0.2.1")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "multicodecs" version))
        (sha256
          (base32
            "0drq267di57l9zqw6zvqqimilz42rbc8z7392dwkk8wslq30s7v8"))))
    (build-system ruby-build-system)
    (synopsis
      "This gem provides a PORO of the multicodec table for use with other
    multiformat ruby gems.")
    (description
      "This gem provides a PORO of the multicodec table for use with other
    multiformat ruby gems.")
    (home-page
      "https://github.com/SleeplessByte/ruby-multicodec")
    (license license:expat)))

(define-public ruby-multihashes
  (package
    (name "ruby-multihashes")
    (version "0.2.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "multihashes" version))
        (sha256
          (base32
            "17wiyy3fiv8rpgdv9ca01yncsmaaf8yg15bg18wc7m9frss1vgqg"))))
    (build-system ruby-build-system)
    (propagated-inputs
      `(("ruby-multicodecs" ,ruby-multicodecs)))
    (synopsis
      "A simple, low-level multihash (https://github.com/jbenet/multihash) implementation for ruby.")
    (description
      "This package provides a simple, low-level multihash (https://github.com/jbenet/multihash) implementation for ruby.")
    (home-page
      "https://github.com/neocities/ruby-multihashes")
    (license license:expat)))

(define-public ruby-lazy-object
  (package
    (name "ruby-lazy-object")
    (version "0.0.3")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "lazy_object" version))
        (sha256
          (base32
            "08px15lahc28ik9smvw1hgamf792gd6gq0s4k94yq1h7jq25wjn8"))))
    (build-system ruby-build-system)
    (arguments
     '(#:test-target "spec"))
    (synopsis
      "It's an object wrapper that forwards all calls to the reference object. This object is not created until the first method dispatch.")
    (description
      "It's an object wrapper that forwards all calls to the reference object.  This object is not created until the first method dispatch.")
    (home-page "")
    (license license:expat)))

(define-public ruby-citrus
  (package
    (name "ruby-citrus")
    (version "3.0.2")
    (source
     (origin
       (method git-fetch)
       ;; Download from GitHub because the rubygems version does not contain
       ;; files needed for tests
       (uri (git-reference
             (url "https://github.com/mjackson/citrus")
             (commit (string-append "v" version))))
       (file-name (git-file-name name version))
       (sha256
        (base32
         "197wrgqrddgm1xs3yvjvd8vkvil4h4mdrcp16jmd4b57rxrrr769"))))
    (build-system ruby-build-system)
    (synopsis "Parsing Expressions for Ruby")
    (description "Parsing Expressions for Ruby")
    (home-page "http://mjackson.github.io/citrus")
    (license license:expat)))

(define-public ruby-cbor
  (package
    (name "ruby-cbor")
    (version "0.5.9.6")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "cbor" version))
        (sha256
          (base32
            "0511idr8xps9625nh3kxr68sdy6l3xy2kcz7r57g47fxb1v18jj3"))))
    (build-system ruby-build-system)
    (arguments
     '(#:test-target "spec"))
    (native-inputs
     `(("ruby-rspec" ,ruby-rspec)
       ("ruby-rake-compiler" ,ruby-rake-compiler)
       ("ruby-yard" ,ruby-yard)))
    (synopsis
      "CBOR is a library for the CBOR binary object representation format, based on Sadayuki Furuhashi's MessagePack library.")
    (description
      "CBOR is a library for the CBOR binary object representation format, based on Sadayuki Furuhashi's MessagePack library.")
    (home-page "http://cbor.io/")
    (license license:asl2.0)))

(define-public ruby-gem-release
  (package
    (name "ruby-gem-release")
    (version "2.2.2")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "gem-release" version))
        (sha256
          (base32
            "108rrfaiayi14zrqbb6z0cbwcxh8n15am5ry2a86v7c8c3niysq9"))))
    (build-system ruby-build-system)
    (arguments
     ;; No rakefile
     `(#:tests? #f))
    (synopsis
      "Release your ruby gems with ease. (What a bold statement for such a tiny plugin ...)")
    (description
      "Release your ruby gems with ease. (What a bold statement for such a tiny plugin ...)")
    (home-page
    "https://github.com/svenfuchs/gem-release")
    (license license:expat)))

(define-public ruby-base32
  (package
    (name "ruby-base32")
    (version "0.3.4")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "base32" version))
        (sha256
          (base32
            "1fjs0l3c5g9qxwp43kcnhc45slx29yjb6m6jxbb2x1krgjmi166b"))))
    (build-system ruby-build-system)
    (native-inputs
     `(("ruby-gem-release" ,ruby-gem-release)))
    (synopsis
      "Ruby extension for base32 encoding and decoding")
    (description
      "Ruby extension for base32 encoding and decoding")
    (home-page "https://github.com/stesla/base32")
    (license license:expat)))

(define-public ruby-dhall
  (package
    (name "ruby-dhall")
    (version "0.5.3.fixed")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "dhall" version))
        (sha256
          (base32
            "1qn7fpiakzpllks43m7r3wh6a2rypxgg02y09zzk27lhqv6bbbrz"))))
    (build-system ruby-build-system)
    (arguments
     ;; No test in gem archive
     `(#:tests? #f))
    (propagated-inputs
      `(("ruby-base32" ,ruby-base32)
        ("ruby-cbor" ,ruby-cbor)
        ("ruby-citrus" ,ruby-citrus)
        ("ruby-lazy-object" ,ruby-lazy-object)
        ("ruby-multihashes" ,ruby-multihashes)
        ("ruby-promise.rb" ,ruby-promise.rb)
        ("ruby-value-semantics" ,ruby-value-semantics)))
    (synopsis
      "This is a Ruby implementation of the Dhall configuration language. Dhall is a powerful, but safe and non-Turing-complete configuration language. For more information, see: https://dhall-lang.org")
    (description
      "This is a Ruby implementation of the Dhall configuration language.  Dhall is a powerful, but safe and non-Turing-complete configuration language.  For more information, see: https://dhall-lang.org")
    (home-page
      "https://git.sr.ht/~singpolyma/dhall-ruby")
    (license license:gpl3)))

(define-public ruby-roda
  (package
    (name "ruby-roda")
    (version "3.47.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "roda" version))
        (sha256
          (base32
            "1g3zs4bk8hqii15ci1hsykcsya88vr2qv63gp1qbcx4bm14l8lkl"))))
    (build-system ruby-build-system)
    (arguments
     ;; No rakefile
     `(#:tests? #f))
    (propagated-inputs `(("ruby-rack" ,ruby-rack)))
    (synopsis "Routing tree web toolkit")
    (description "Routing tree web toolkit")
    (home-page "http://roda.jeremyevans.net")
    (license license:expat)))

(define-public ruby-sentry-core
  (package
    (name "ruby-sentry-core")
    (version "4.3.1")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "sentry-ruby-core" version))
        (sha256
          (base32
            "13z35s9mflh3v775a0scsnqhscz9q46kaak38y7zmx32z7sg2a3a"))))
    (build-system ruby-build-system)
    (arguments
     ; No rakefile in gem
     '(#:tests? #f))
    (propagated-inputs
      `(("ruby-concurrent" ,ruby-concurrent)
        ("ruby-faraday" ,ruby-faraday)))
    (synopsis
      "A gem that provides a client interface for the Sentry error logger")
    (description
      "This package provides a gem that provides a client interface for the Sentry error logger")
    (home-page
      "https://github.com/getsentry/sentry-ruby")
    (license license:expat)))

(define-public ruby-sentry
  (package
    (name "ruby-sentry")
    (version "4.3.1")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "sentry-ruby" version))
        (sha256
          (base32
            "101q3141xfkmh7vi8h4sjqqmxcx90xhyq51lmfnhfiwgii7cn9k8"))))
    (build-system ruby-build-system)
    (arguments
     ; No rakefile in gem
     '(#:tests? #f))
    (propagated-inputs
      `(("ruby-concurrent" ,ruby-concurrent)
        ("ruby-faraday" ,ruby-faraday)
        ("ruby-sentry-core" ,ruby-sentry-core)))
    (synopsis
      "A gem that provides a client interface for the Sentry error logger")
    (description
      "This package provides a gem that provides a client interface for the Sentry error logger")
    (home-page
      "https://github.com/getsentry/sentry-ruby")
    (license license:expat)))

(define-public ruby-em-socksify
  (package
    (name "ruby-em-socksify")
    (version "0.3.2")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "em-socksify" version))
        (sha256
          (base32 "0rk43ywaanfrd8180d98287xv2pxyl7llj291cwy87g1s735d5nk"))))
    (build-system ruby-build-system)
    (arguments
     ;; Tests depend on external network
     '(#:tests? #f))
    (propagated-inputs `(("ruby-eventmachine" ,ruby-eventmachine)))
    (synopsis "Transparent proxy support for any EventMachine protocol")
    (description "Transparent proxy support for any EventMachine protocol")
    (home-page "https://github.com/igrigorik/em-socksify")
    (license license:expat)))

(define-public ruby-rspec-collection-matchers
  (package
    (name "ruby-rspec-collection-matchers")
    (version "1.2.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "rspec-collection_matchers" version))
        (sha256
          (base32 "1864xlxl7mi6mvjyp85a0gc10cyvpf6bj8lc86sf8737wlzn12ks"))))
    (build-system ruby-build-system)
    (arguments
     `(#:test-target "spec"
       #:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'no-bundler
           (lambda _
             (substitute* "Rakefile"
               (("Bundler.setup") "\n"))
             #t)))))
    (propagated-inputs `(("ruby-rspec-expectations" ,ruby-rspec-expectations)))
    (native-inputs
     `(("ruby-activemodel" ,ruby-activemodel)
       ("ruby-cucumber" ,ruby-cucumber)
       ("ruby-rspec" ,ruby-rspec)))
    (synopsis
      "Collection cardinality matchers, extracted from rspec-expectations")
    (description
      "Collection cardinality matchers, extracted from rspec-expectations")
    (home-page "https://github.com/rspec/rspec-collection_matchers")
    (license license:expat)))

(define-public ruby-cookiejar
  (package
    (name "ruby-cookiejar")
    (version "0.3.3")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "cookiejar" version))
        (sha256
          (base32 "0q0kmbks9l3hl0wdq744hzy97ssq9dvlzywyqv9k9y1p3qc9va2a"))))
    (build-system ruby-build-system)
    (native-inputs
     `(("ruby-rspec" ,ruby-rspec)
       ("ruby-rspec" ,ruby-rspec-collection-matchers)
       ("ruby-yard" ,ruby-yard)))
    (synopsis
      "Allows for parsing and returning cookies in Ruby HTTP client code")
    (description
      "Allows for parsing and returning cookies in Ruby HTTP client code")
    (home-page "http://alkaline-solutions.com")
    (license #f)))

(define-public ruby-em-http-request
  (package
    (name "ruby-em-http-request")
    (version "1.1.7")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "em-http-request" version))
        (sha256
          (base32 "1azx5rgm1zvx7391sfwcxzyccs46x495vb34ql2ch83f58mwgyqn"))))
    (build-system ruby-build-system)
    (arguments
     ; Tests require too-old rake and use unmaintained mongrel
     '(#:tests? #f
       #:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'no-bundler
           (lambda _
             (substitute* "spec/helper.rb"
               (("require 'bundler/setup'") "require 'mongrel'\n"))
             (substitute* "spec/helper.rb"
               (("blk.call if system.*") "false\n"))
             #t)))))
    (propagated-inputs
      `(("ruby-addressable" ,ruby-addressable)
        ("ruby-cookiejar" ,ruby-cookiejar)
        ("ruby-em-socksify" ,ruby-em-socksify)
        ("ruby-eventmachine" ,ruby-eventmachine)
        ("ruby-http-parser.rb" ,ruby-http-parser.rb)))
    ;(native-inputs
    ; `(("ruby-rspec" ,ruby-rspec)
    ;   ("ruby-rack" ,ruby-rack)
    ;   ("ruby-mongrel" ,ruby-mongrel)
    ;   ("ruby-multi-json" ,ruby-multi-json)))
    (synopsis "EventMachine based, async HTTP Request client")
    (description "EventMachine based, async HTTP Request client")
    (home-page "http://github.com/igrigorik/em-http-request")
    (license license:expat)))

(define-public ruby-em-promise.rb
  (package
    (name "ruby-em-promise.rb")
    (version "0.0.4")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "em_promise.rb" version))
        (sha256
          (base32 "1qkxj57fry6vigpzmgi4i6i9yzw0gsvf6wgpx1c0xvyq8wyaww0z"))))
    (build-system ruby-build-system)
    (arguments
     ; No rakefile in gem
     '(#:tests? #f))
    (propagated-inputs
      `(("ruby-eventmachine" ,ruby-eventmachine)
        ("ruby-promise.rb" ,ruby-promise.rb)))
    (synopsis "A subclass of promise.rb Promise for EventMachine.")
    (description
      "This package provides a subclass of promise.rb Promise for EventMachine.")
    (home-page "https://git.singpolyma.net/em_promise.rb")
    (license #f)))

(define-public ruby-geoip
  (package
    (name "ruby-geoip")
    (version "1.6.4")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "geoip" version))
        (sha256
          (base32 "1if16n4pjl2kshc0cqg7i03m55fspmlca6p9f4r66rpzw0v4d6jc"))))
    (build-system ruby-build-system)
    (arguments
     ;; No rakefile
     `(#:tests? #f))
    (synopsis
      "GeoIP searches a GeoIP database for a given host or IP address, and
returns information about the country where the IP address is allocated,
and the city, ISP and other information, if you have that database version.")
    (description
      "GeoIP searches a GeoIP database for a given host or IP address, and
returns information about the country where the IP address is allocated,
and the city, ISP and other information, if you have that database version.")
    (home-page "http://github.com/cjheath/geoip")
    (license #f)))

(define-public ruby-sixarm-ruby-unaccent
  (package
    (name "ruby-sixarm-ruby-unaccent")
    (version "1.2.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "sixarm_ruby_unaccent" version))
        (sha256
          (base32 "11237b8r8p7fc0cpn04v9wa7ggzq0xm6flh10h1lnb6zgc3schq0"))))
    (build-system ruby-build-system)
    (native-inputs (list ruby-coveralls ruby-simplecov))
    (synopsis
      "Unaccent replaces a string's accented characters with unaccented characters")
    (description
      "Unaccent replaces a string's accented characters with unaccented characters")
    (home-page "http://sixarm.com/")
    (license (list #f #f #f #f license:expat #f))))

(define-public ruby-simple-po-parser
  (package
    (name "ruby-simple-po-parser")
    (version "1.1.5")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "simple_po_parser" version))
        (sha256
          (base32 "134zg0dzd7216lyczkhv01v27ikkmipjihcy2bzi0qv72p1i923i"))))
    (build-system ruby-build-system)
    (arguments
     `(#:test-target "spec"
       #:phases
       (modify-phases %standard-phases
         (add-after 'extract-gemspec 'remove-rakefile-self-reference
           (lambda _
             (substitute* "Rakefile"
               ((".*simple_po_parser.*") "")))))))
    (native-inputs (list ruby-rspec))
    (synopsis
      "A simple PO file to ruby hash parser . PO files are translation files generated by GNU/Gettext tool.")
    (description
      "This package provides a simple PO file to ruby hash parser .  PO files are
translation files generated by GNU/Gettext tool.")
    (home-page "http://github.com/experteer/simple_po_parser")
    (license license:expat)))

(define-public ruby-i18n-data
  (package
    (name "ruby-i18n-data")
    (version "0.15.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "i18n_data" version))
        (sha256
          (base32 "0xkir0c60420h7khvlkmh9ymkdn6fnzppn5pbghzsxlypfwbg355"))))
    (build-system ruby-build-system)
    (arguments
     ;; No rakefile
     `(#:tests? #f))
    (propagated-inputs (list ruby-simple-po-parser))
    (synopsis
      "country/language names and 2-letter-code pairs, in 85 languages")
    (description
      "country/language names and 2-letter-code pairs, in 85 languages")
    (home-page "https://github.com/grosser/i18n_data")
    (license license:expat)))

(define-public ruby-geocoder
  (package
    (name "ruby-geocoder")
    (version "1.7.3")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "geocoder" version))
        (sha256
          (base32 "1g6w1x5fjc84s9qkh2wj3j7gjig6hlj87f4vzh41mj6scbpssp3r"))))
    (build-system ruby-build-system)
    (arguments
     ;; No rakefile
     `(#:tests? #f))
    (synopsis
      "Object geocoding (by street or IP address), reverse geocoding (coordinates to street address), distance queries for ActiveRecord and Mongoid, result caching, and more. Designed for Rails but works with Sinatra and other Rack frameworks too.")
    (description
      "Object geocoding (by street or IP address), reverse geocoding (coordinates to
street address), distance queries for ActiveRecord and Mongoid, result caching,
and more.  Designed for Rails but works with Sinatra and other Rack frameworks
too.")
    (home-page "http://www.rubygeocoder.com")
    (license license:expat)))

(define-public ruby-retryable
  (package
    (name "ruby-retryable")
    (version "3.0.5")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "retryable" version))
        (sha256
          (base32 "0pymcs9fqcnz6n6h033yfp0agg6y2s258crzig05kkxs6rldvwy9"))))
    (build-system ruby-build-system)
    (native-inputs (list ruby-rspec ruby-yard ruby-simplecov))
    (synopsis "Retrying code blocks in Ruby")
    (description "Retrying code blocks in Ruby")
    (home-page "http://github.com/nfedyashev/retryable")
    (license license:expat)))

(define-public ruby-money
  (package
    (name "ruby-money")
    (version "6.16.0")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "money" version))
        (sha256
          (base32 "0jkmsj5ymadik7bvl670bqwmvhsdyv7hjr8gq9z293hq35gnyiyg"))))
    (build-system ruby-build-system)
    (arguments
     ;; No rakefile
     `(#:tests? #f))
    (propagated-inputs (list ruby-i18n))
    (synopsis "A Ruby Library for dealing with money and currency conversion.")
    (description
      "This package provides a Ruby Library for dealing with money and currency
conversion.")
    (home-page "https://rubymoney.github.io/money")
    (license license:expat)))

(define-public ruby-countries
  (package
    (name "ruby-countries")
    (version "4.2.1")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "countries" version))
        (sha256
          (base32 "18yqd6rlv84nmzkfxf5hvcydyf6jv3c715dq6cpwqyandqd6j264"))))
    (build-system ruby-build-system)
    (arguments
     `(#:test-target "spec"))
    (propagated-inputs (list ruby-i18n-data ruby-sixarm-ruby-unaccent))
    (native-inputs
      (list
        ruby-activesupport
        ruby-rspec
        ruby-geocoder
        ruby-money
        ruby-retryable))
    (synopsis
      "All sorts of useful information about every country packaged as pretty little country objects. It includes data from ISO 3166")
    (description
      "All sorts of useful information about every country packaged as pretty little
country objects.  It includes data from ISO 3166")
    (home-page "http://github.com/countries/countries")
    (license license:expat)))

(define-public ruby-em-pg-client
  (package
    (name "ruby-em-pg-client")
    (version "0.3.4-585f186")
    (source
     (origin
       (method git-fetch)
       (uri (git-reference
             (url "https://github.com/royaltm/ruby-em-pg-client")
             (commit "585f1861b7d7e181aa787332f7def795d45e62f5")))
       (file-name (git-file-name name version))
       (sha256
        (base32
         "10l3jj8ij5bxa5838n4z6sq3pg97p422ykhl7i58xr6hs54c5jgy"))))
    (build-system ruby-build-system)
    (arguments
     `(#:test-target "spec"))
    (propagated-inputs (list ruby-eventmachine ruby-pg))
    (native-inputs (list ruby-coveralls ruby-simplecov))
    (synopsis
      "PostgreSQL asynchronous EventMachine client, based on pg interface (PG::Connection)")
    (description
      "PostgreSQL asynchronous EventMachine client, based on pg interface
(PG::Connection)")
    (home-page "http://github.com/royaltm/ruby-em-pg-client")
    (license license:expat)))

;;;;

(define %source-dir (dirname (current-filename)))
(define %git-dir (string-append %source-dir "/.git"))

; Bake a template by eval'ing the leaves
(define-public (bake tmpl)
 (list
  (car tmpl)
  (cons (caadr tmpl) (map
   (lambda (x) (list (car x) (eval (cadr x) (current-module))))
   (cdadr tmpl)))))

; double-escaped template of the jmp-register sexp
; This allows us to bake the expression without doing a full eval to a record,
; so it can be written
(define-public jmp-register-template
  '((package-input-rewriting `((,ruby-eventmachine . ,ruby-eventmachine-openssl)))
  (package
    (name "jmp-register")
    (version (read-line (open-pipe* OPEN_READ "git" "--git-dir" %git-dir "describe" "--always" "--dirty")))
    (source
     `(origin
       (method git-fetch)
       (uri (git-reference
             (recursive? #t)
             (url "https://gitlab.com/ossguy/jmp-register")
             (commit ,(read-line (open-pipe* OPEN_READ "git" "--git-dir" %git-dir "rev-parse" "HEAD")))))
       (file-name (git-file-name name version))
       (sha256
        (base32
         ,(read-line (open-pipe* OPEN_READ "guix" "hash" "-rx" %source-dir))))))
    (build-system 'copy-build-system)
    (arguments
     '`(#:install-plan '(
         ("." "share/jmp-register" #:exclude
           ("config.dhall.sample" "Makefile" "Gemfile" "jmp-register.scm"
            "README.md" "COPYING") #:exclude-regexp ("^\\./(\\.|assets)"))
         ("assets/js" "share/jmp-register/public/assets/")
         ("README.md" ,(string-append "share/doc/jmp-register-" version "/"))
         ("config.dhall.sample" ,(string-append "share/doc/jmp-register-" version "/")))
        #:phases
       (modify-phases %standard-phases
         (add-after 'install 'runner
           (lambda* (#:key outputs #:allow-other-keys)
             (use-modules (ice-9 ftw))
             (let* ((out (assoc-ref outputs "out"))
                    (appdir (string-append out "/share/jmp-register"))
                    (bindir (string-append out "/bin/")))
               (mkdir-p bindir)
               (let ((binstub (string-append bindir "jmp-register")))
                 (call-with-output-file binstub
                   (lambda (port)
                     (format port
                       "#!~a~@
                        ENV['GEM_PATH'] = ['~a', ENV['GEM_PATH']].compact.join(':')~@
                        ENV['RACK_ENV'] = 'production' unless ENV.key?('RACK_ENV')~@
                        Gem.clear_paths~@
                        Dir.chdir '~a'~@
                        load '~a'~%"
                       (which "ruby")
                       (getenv "GEM_PATH") ; https://lists.gnu.org/archive/html/guix-devel/2021-09/msg00336.html
                       appdir
                       (which "rackup"))))
                 (chmod binstub #o755)))
             #t))
         (add-before 'install 'build-assets
           (lambda _
             (invoke "make")
             (mkdir-p "public/assets/css/global")
             (mkdir-p "public/assets/css/tom_select")
             (for-each (lambda (file)
               (invoke "sassc" "-texpanded" file (string-append "public/" file ".css")))
               (find-files "assets" "^[^_].*\\.scss$"))
             #t)))))
    (inputs
      '(list
        ruby-blather
        ruby-countries
        ruby-dhall
        ruby-em-http-request
        ruby-em-promise.rb
        ruby-em-pg-client
        ruby-geoip
        ruby-multi-json
        ruby-em-hiredis
        ruby-roda
        ruby-sentry
        ruby-slim
        ruby-statsd-instrument+graphite
        ruby-value-semantics
        ruby
        ruby-thin))
    (native-inputs '(list dhall sassc))
    (synopsis
      "JMP homepage and registration stub")
    (description "")
    (home-page
      "https://gitlab.com/ossguy/jmp-register")
    (license 'license:agpl3))))

; Baked version of jmp-register-template with leaves eval'd
(define-public jmp-register-baked
  (bake jmp-register-template))

; Build clean from git the version from a local clone
; To build whatever is sitting in local use:
; guix build --with-source=$PWD -f guix.scm

(eval jmp-register-baked (current-module))
