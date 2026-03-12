#!/bin/bash
# Wait for MSSQL to be fully ready (healthcheck may pass before DB accepts complex queries)
sleep 10

echo "Running MSSQL seed script..."
/opt/mssql-tools18/bin/sqlcmd -S mssql -U sa -P 'G8test@Pass1' -C -i /seeds/01-init.sql

if [ $? -eq 0 ]; then
    echo "MSSQL seed completed successfully."
else
    echo "MSSQL seed failed."
    exit 1
fi
