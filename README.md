# Tracker App

The Tracker App is designed to help you efficiently manage tasks and keep track of your progress. To ensure a smooth installation process, follow the steps below:

>Before running the application, there are a few additional steps to configure

1. **Generate an application key:**

    ```bash
    php artisan key:generate
    ```

2. **Create the symbolic link for the storage:**

    ```bash
    php artisan storage:link
    ```

3. **Set timezone on .env file**

    ```
    APP_TIMEZONE = Asia/Kolkata
    ```

4. **Migrate tables and seed:**

    ```bash
    php artisan migrate:fresh --seed
    ```