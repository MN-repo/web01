(define-module (jmp-register)
  #:use-module ((guix licenses) #:prefix license:)
  #:use-module (guix packages)
  #:use-module (guix download)
  #:use-module (guix git-download)
  #:use-module (guix build-system ruby)
  #:use-module (guix build-system copy)
  #:use-module (gnu packages dhall)
  #:use-module (gnu packages ruby)
  #:use-module (gnu packages rails)
  #:use-module (gnu packages databases)
  #:use-module (gnu packages web)
  #:use-module (ice-9 rdelim)
  #:use-module (ice-9 popen)
)

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
                ((".*bluecloth.*") "\n"))
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
    (version "0.5.2")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "dhall" version))
        (sha256
          (base32
            "09wcq8xc1ynld04r2f332bx8cn7rjc4afaq8hm1dr2fc35jlpn6m"))))
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
    (version "0.0.3")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "em_promise.rb" version))
        (sha256
          (base32 "1wm7n6plx8zkknamr95s3zy6hxdl69yac5yb7hq4m87f76xwbrh6"))))
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

;;;;

(define %source-dir (dirname (current-filename)))
(define %git-dir (string-append %source-dir "/.git"))
(define %module (current-module))

; Bake a template by eval'ing the leaves
(define-public (bake tmpl)
 (cons
  (car tmpl)
  (map
   (lambda (x) (list (car x) (eval (cadr x) %module)))
   (cdr tmpl))))

; double-escaped template of the jmp-register sexp
; This allows us to bake the expression without doing a full eval to a record,
; so it can be written
(define-public jmp-register-template
  '(package
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
            "README.md" "COPYING") #:exclude-regexp ("^\\./\\."))
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
             (mkdir-p "public/assets/css")
             (invoke "sassc" "-texpanded" "assets/css/style.scss" "public/assets/css/style.scss.css")
             (delete-file-recursively "assets")
             #t)))))
    (inputs
      '`(("ruby-blather" ,ruby-blather)
        ("ruby-dhall" ,ruby-dhall)
        ("ruby-em-http-request" ,ruby-em-http-request)
        ("ruby-em-promise.rb" ,ruby-em-promise.rb)
        ("ruby-geoip" ,ruby-geoip)
        ("ruby-multi-json" ,ruby-multi-json)
        ("ruby-redis" ,ruby-redis)
        ("ruby-roda" ,ruby-roda)
        ("ruby-sentry" ,ruby-sentry)
        ("ruby-slim" ,ruby-slim)
        ("ruby" ,ruby)
        ("ruby-thin" ,ruby-thin)))
    (native-inputs
     '`(("dhall" ,dhall)
        ("sassc" ,sassc)))
    (synopsis
      "JMP homepage and registration stub")
    (description "")
    (home-page
      "https://gitlab.com/ossguy/jmp-register")
    (license 'license:agpl3)))

; Build clean from git the version from a local clone
; To build whatever is sitting in local use:
; guix build --with-source=jmp-register=$PWD -L. jmp-register
(define-public jmp-register
  (eval (bake jmp-register-template) %module))
