# Gibbon Docker Image
Gibbon School Management System Docker Image
## Supported tags and respective `Dockerfile` links


* [`23.0.02`, `23.0.02.01`, `24.0.0`, `latest` (Dockerfile)](https://github.com/matiaspagano/gibbon-docker/blob/main/Dockerfile)

## Configuration

## Environment variables

When you start the Gibbon image, you can adjust the configuration of the instance by passing one or more environment variables either on the docker-compose file or on the `docker run` command line. If you want to add a new environment variable:

 * For docker-compose add the variable name and value under the application section in the [`docker-compose.yml`](https://github.com/matiaspagano/gibbon-docker/blob/main/docker-compose.yml) file present in this repository:

```yaml
gibbon:
  ...
  environment:
    GIBBON_AUTOINSTALL: 1
  ...
```

 * For manual execution add a `--env` option with each variable and value:

  ```console
  $ docker run -d --name gibbon \
    --env GIBBON_AUTOINSTALL=1 \
    matiaspagano/gibbon:latest
  ```

Available environment variables:

##### Autoinstall

- `GIBBON_AUTOINSTALL`: Runs the autoinstall script with the following variables if the value is **1**; otherwise, a normal installation is required. 
##### User and Site configuration (Required with Autoinstall)

- `GIBBON_USERNAME`: Gibbon application username. 
- `GIBBON_PASSWORD`: Gibbon application password.
- `GIBBON_EMAIL`: Gibbon application email.
- `GIBBON_URL`: Gibbon external URL. 
##### Initialize an existing database (Required with Autoinstall)

- `DB_HOST`: Hostname for database server.
- `DB_NAME`: Database name that Gibbon will use to connect with the database. 
- `DB_USER`: Database user that Gibbon will use to connect with the database.
- `DB_PASSWORD`: Database password that Gibbon will use to connect with the database.

##### Other User and Site configuration

- `GIBBON_SYSTEM_NAME`: Gibbon site name. Default: **Gibbon**
- `GIBBON_INSTALL_TYPE`: Install type Testing/Production. Default: **Production**
- `GIBBON_TIMEZONE`: Timezone for Gibbon instance. Default: **America/Argentina/Buenos_Aires**
- `GIBBON_COUNTRY`: Country for Gibbon instance. Default: **Argentina**
- `GIBBON_CURRENCY`: Currency for Gibbon instance. Default: **ARS $**
- `GIBBON_ORGANISATION_NAME`: Gibbon organization name. Default: **Gibbon**
- `GIBBON_ORGANISATION_INITIALS`: Gibbon organization initials. Default: **Gibbon**
