<?php

/**
 * @file
 */

$metadata['http://federation.asu.edu/adfs/services/trust'] = [
  'entityid' => 'http://federation.asu.edu/adfs/services/trust',
  'contacts' => [
        [
          'contactType' => 'support',
        ],
  ],
  'metadata-set' => 'saml20-idp-remote',
  'SingleSignOnService' => [
        [
          'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
          'Location' => 'https://federation.asu.edu/adfs/ls/',
        ],
        [
          'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
          'Location' => 'https://federation.asu.edu/adfs/ls/',
        ],
  ],
  'SingleLogoutService' => [
        [
          'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
          'Location' => 'https://federation.asu.edu/adfs/ls/',
        ],
        [
          'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
          'Location' => 'https://federation.asu.edu/adfs/ls/',
        ],
  ],
  'ArtifactResolutionService' => [],
  'NameIDFormats' => [
    'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
    'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
  ],
  'keys' => [
        [
          'encryption' => TRUE,
          'signing' => FALSE,
          'type' => 'X509Certificate',
          'X509Certificate' => 'MIIC5jCCAc6gAwIBAgIQf1V5a4W5dplB/Lu/Xn5bEjANBgkqhkiG9w0BAQsFADAvMS0wKwYDVQQDEyRBREZTIEVuY3J5cHRpb24gLSBmZWRlcmF0aW9uLmFzdS5lZHUwHhcNMjUwOTA2MDExODIzWhcNMjgwOTA1MDExODIzWjAvMS0wKwYDVQQDEyRBREZTIEVuY3J5cHRpb24gLSBmZWRlcmF0aW9uLmFzdS5lZHUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCvQGAa2JDJpcTaAXyTLyw5xL6gWLe6DDxFlPUUktGclKApUUH9O3cwHxMCTdUr/lhT8sNPp6Hv4Nm4hhLXLRL9bPtQjd+0h2w12CaEZq3eplU+McDN1SV+G9fMA4QCOV8OQIIX0m+OsU7WGC3hBOi5xfThtISdM1Rk+xiDMCWWIglJKDDAXsd26WqSDRgcf1fTLczdsSTrmdVwq3mFS484cy4u5GQ5DWnKChVd0gp51A6m2VyrMOubAPQntK1XXWZ74gTqPhxQP3NnXjwWSI2XY8tpB2pFU3OLyTxIO4NtE3VtCPWd/aRSLStBn3C1frOilj6wrdY5YOUx60SAzz6BAgMBAAEwDQYJKoZIhvcNAQELBQADggEBACrUwiDtQpirHBMID1ELUqlTYq6ggOL2RZNdePsxsjly/JrZaroF4lUERY9tROoVXSLKTPTEQdTEjRNQT8p1l3og7x94GqzMBXJMnkfI4uLCecv4GfjuGwauJRHc0oa3ZyUOngjRZPgtk+qEcIb+AGBurCWhGi0DbFcwAkG74EVTGTzGnSesYQbSEuFXPKbYzWkM9l13Hro9rPf8aXSH58O1dURpThmvdHGSNcnbUg85YTyXudLBSHddHcSH2WaNKN0bZcRZpZ4AmKSv4Yy+a5jCQ4G0IB1RNPpF3QkhZozfpWT2A/1C1BZdI73UnTRcOPqLmKc6VzlarN5lkYnfcbI=',
        ],
        [
          'encryption' => FALSE,
          'signing' => TRUE,
          'type' => 'X509Certificate',
          'X509Certificate' => 'MIIE4DCCAsigAwIBAgIQPomoIEYWwLlB8viZoQ9P/jANBgkqhkiG9w0BAQsFADAsMSowKAYDVQQDEyFBREZTIFNpZ25pbmcgLSBmZWRlcmF0aW9uLmFzdS5lZHUwHhcNMjYwNTI2MTczNDM5WhcNMjkwNTI1MTczNDM5WjAsMSowKAYDVQQDEyFBREZTIFNpZ25pbmcgLSBmZWRlcmF0aW9uLmFzdS5lZHUwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQDTj73RUsQbEAhm18IhJvyOeOqFSHUCD8+NKFoqeYD8YBOcqIKkopmy1jt/2qApR6wcV6UqjHKvrAs+OQgFjmEBoAAZU0N6ohHJnDbN5vQDfODVBkn3db5F1FuVZSxwcBtzclwtg33pm4E8veyiP7+ZeRs4but6gQkc61weC0ucs9K8pPVlNIeyNCCLSXQx1BgPE4dR7tsvcRKp5/qY24OEKlmMe+YjzhJbGToS4rcO0fjDQKY6MVnLIen0scV+mj4FSUcZjup6hHIdiS/+V89XsItU1v0FrO4Wnki+UbZAzoUnXlp8W4+EhajDwTRU59OX6d38Yq1fngEh6rT84QHIA0w7AH5S7P/fvDWD5VaHvR25Cse0kEyMzkpiOomjb80JQP+no+slrDjSHlB1ThuMk4FQ3LhxVcuJpF3DegnBJStXiThmbfh9WillZwVIiHY7FKE4uSj5GDluhHNdWOfIwBaIF9ZK+yQ1MSR9WdOrqGY0C1IkgaaN61uyZ+KfQ29ryJlzsL9uX2Qcinnj+Njo4gnxszA9Ftgjp2uQfSDukQnj3pZF2YZSpoXLNEfwkXGAKhQlYsSVyNhO+98T3BUJnG9AAwTYAPZBkCeRkYCizekZgEuSFQj+HowK6eRuFNk8rupFP/r6rJCGP5CQIbw+SHrCN/c/8ryJjXxMX5vitQIDAQABMA0GCSqGSIb3DQEBCwUAA4ICAQAxJ6Dn/DxMNnH6FqRlXEEYzP3DKdEHB1rrVAy76rtPLmgGmBlWk/YB0+uORmbErhPxGsmu13/cPqdqUD4Z2S+bgHGpOQEHD/jt3a09SlMEn3FmgPa3zuY/MsbijPoVjPbOaWT2oJxnHVECICI5+CsxIR2+zM+BNxcGHT4Voe1EJDo9MAx3AgcTBQDAI9+J7zaBGQsQlo5gJrPHPh4KkjicN4LnFLENI31qDPyA3je986CbNjIVWSZh7qvXJ8oo36PDBgmOcLgw5ytVeypIEZliTyi7oUARMZB7wTDkWsPxV+Ds7wIaKl8sBUbUHi9HXJspoAJzPEpgCKEBJqaKg+0Gs2ML5BpTNk6O/exC5SE+na9JSPL/TR5UwQp7BFj/KITIJfeAap040R8C1lMnnoYzirpkBuyfERRQroWE4KjhYkhl13NC/gdVgjLjsGYajqUMj/SSvT3aQQKQHZ6/qQlD2fFz7iGcDzqb1oqPZCXlzJ6RMkBv9x683KRe5iVElovdpbe6eQGLozcQbwNdJdbX7rDiKT8oFf+a28iMIUXses7/B3ptPd2CXxGJT98z2muxPiFNqd/Rk+9BN2BIJQdVVy1vSMD/xSSuTpTZYQ+uAmvNRdYWD4hHfn+igVssXKxGIGb0zsfmw9fakAhXC8I4XDjG/t49HelSyjFq24pyrA==',
        ],
        [
          'encryption' => FALSE,
          'signing' => TRUE,
          'type' => 'X509Certificate',
          'X509Certificate' => 'MIIE4DCCAsigAwIBAgIQG1nPNc0CrJdPbCcfTJG9bDANBgkqhkiG9w0BAQsFADAsMSowKAYDVQQDEyFBREZTIFNpZ25pbmcgLSBmZWRlcmF0aW9uLmFzdS5lZHUwHhcNMjYwNTI2MTczNDU1WhcNMjkwNTI1MTczNDU1WjAsMSowKAYDVQQDEyFBREZTIFNpZ25pbmcgLSBmZWRlcmF0aW9uLmFzdS5lZHUwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQDk2Oc7TMFu5waN432UWt3tiHZM4/QhGZS3qQPTReOifTbOt5MkF18e0nQ3Zgf8Qz/G4HfMxkldz7yed5CYQmUChrVFLuZREzQEVvQcZ5mX/zqA9uDoMgI/MheCJM8/N308RoJ+PcCFl+iTQXxUw3ERm6KpON8tfMQ1zbdQR5pug1spEXbedlsbp0PjNbOTUUnvRLNWtXUI+4CN1GX+VgwM+U+Euc20QReU9vAx94WkvAyRm8+UQlvIJp1QEFGxkcYKMDvQAoVIM0czBo5stzcE9ZfZXTjtU7Io4SoDRvHZpluUFJRzelLfiUiTusuSz+QVTLN/3VZ3ias0S/oZY/bZOTdOAoN0kz+QRjWxL+Ik5ElmrzoRIgDhAEXqZ4gcTDecqwxMlAtywOmNk6zIXG8MG62Cl1OdYMm/GgSxCKjq8wNeB1wTN0bNvb+fEVjqGZ6HJthwT92VgNqLUOQZqznPOculoSLaY0WfdqCbFJ5C5CHp2L8kLM/p43EGilycmWxkQG8UR+8dm73HrvmOYrub/laTvqB/OyDQrIqBdwVKAjpeRt+rJm8vbhYyu+XbOKdFUeGPVfANXAYdJYR/dTdh4RnvQ6EYAjyj1g6Vt+1gj8eImjrG8BuYqW9W03IKRb7avhmag1j94k5F2o7AjlqWfSQiZYSLZL0Oc++EbSC3OQIDAQABMA0GCSqGSIb3DQEBCwUAA4ICAQCCE1E3Pus8oC7UGGXhRA9cR9/TiCH+4GVf4hNbvTn7HP8ZvuDTUoz9CA768NyB5cBaLh3LB3Zl6fYW+Dpqpoaj9aE6uEco4BjC+8AoFCc7bqVgrwQtzPB7ufFVr5+KzLFW2guXnjeZTXXVPOb9iaNplI/C8batgu39QHrkjNiUHqQkmrqrJ6o3UcarggsQL80oWY+q1LVDI5owKuilnA3q4rX/TUTYa9KoCzXTdkZAkGhkGqg8DOq91rRW3fbkYxbzPs5QksTvz/OMB1njBqnifnFzhNXYaAQeEfmmoYIQB/rHb/UiqYdo3WZSCFLz7hKt8pZ7fN2rTOtDv5CkgY4S3bT/B+yJsippD7rZb8yoWmasMO+jbReLcXxr59jDqs4HhA8L+tTIoHwDxWFUDeunL56G0H6EIy9bZEk2gq0kI+B8YdFQtF0XCggcZRCGWjDyayTxGXJHry3k+rT5sk70JjH+AAR0FQ0vOBqx+wu27Bw7z6BNBbvXhIf+8denJDRK+nElgzb2xlnfxlQNiz1FHHZ/DjoCUHgH8bHRtrlvEzo5qCH741iiyop7beVRqHxx5k0D1hpaoZjfN+yjw9QoZD2bdFq/KLFfURDuVRfGkZFPFyveW8pUCl+jcck6rlCIE9Za82zR92LZrvpDdW0PuGIw5Vx1dpcFB/6m3EqhFw==',
        ],
  ],
];
