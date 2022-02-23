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

(define jmp-register (load "./guix.scm"))

(define-public ruby-interception
  (package
    (name "ruby-interception")
    (version "0.5")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "interception" version))
        (sha256
          (base32 "01vrkn28psdx1ysh5js3hn17nfp1nvvv46wc1pwqsakm6vb1hf55"))))
    (build-system ruby-build-system)
    (native-inputs (list ruby-rspec))
    (synopsis
      "Provides a cross-platform ability to intercept all exceptions as they are raised.")
    (description
      "This package provides a cross-platform ability to intercept all exceptions as
they are raised.")
    (home-page "http://github.com/ConradIrwin/interception")
    (license #f)))

(define-public ruby-pry-rescue
  (package
    (name "ruby-pry-rescue")
    (version "1.5.2")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "pry-rescue" version))
        (sha256
          (base32 "1wn72y8y3d3g0ng350ld92nyjln012432q2z2iy9lhwzjc4dwi65"))))
    (build-system ruby-build-system)
    (arguments
     `(#:phases
       (modify-phases %standard-phases
         (add-after 'unpack 'skip-bogus-test
           (lambda _
             (substitute* "spec/source_location_spec.rb"
               (("time = Time.now") "skip")))))))
    (propagated-inputs (list ruby-interception ruby-pry))
    (native-inputs (list ruby-rspec ruby-pry-stack-explorer))
    (synopsis
      "Allows you to wrap code in Pry::rescue{ } to open a pry session at any unhandled exceptions")
    (description
      "Allows you to wrap code in Pry::rescue{ } to open a pry session at any unhandled
exceptions")
    (home-page "https://github.com/ConradIrwin/pry-rescue")
    (license license:expat)))

(define-public ruby-pry-reload
  (package
    (name "ruby-pry-reload")
    (version "0.3")
    (source
      (origin
        (method url-fetch)
        (uri (rubygems-uri "pry-reload" version))
        (sha256
          (base32 "1gld1454sd5xp2v4vihrhcjh4sgkx7m1kc29qx1nr96r4z2gm471"))))
    (build-system ruby-build-system)
    (arguments
     ;; No tests
     `(#:tests? #f))
    (propagated-inputs (list ruby-listen))
    (synopsis "Tracks and reloads changed files")
    (description "Tracks and reloads changed files")
    (home-page "https://github.com/runa/pry-reload")
    (license #f)))

(concatenate-manifests
  (list
    (packages->manifest
      (list
        ruby-rubocop
        ruby-pry-reload
        ruby-pry-stack-explorer
        ruby-pry-rescue))
    (package->development-manifest jmp-register)))
