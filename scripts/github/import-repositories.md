## github:import-repositories

Usage:
  import-repositories
  import-repositories (-h | --help)

Options:
  -h --help     Show this screen.

Required Environment variables (can be defined in the .env file):
  * INFRA_CONFIG    base path to directory that contains a set files describing your infrastructure
  * GITHUB_USERNAME github username
  * GITHUB_SECRET   github password
  * GITHUB_AUTH_METHOD=http_password    github authentication method
  * GITHUB_ACCOUNT  github account to fetch repositories from
