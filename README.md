# Composer

	composer install -n

# SSH Keys

	mkdir -p /jwt
	openssl genpkey -out /jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	openssl pkey -in /jwt/private.pem -out /jwt/public.pem -pubout

# Environment

    cp .env .env.local
    cp .env.test .env.test.local

# Console

### Create a new admin

    php bin/console create:user <phone> <name>
