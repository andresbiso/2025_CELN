FROM node:18-alpine

WORKDIR /app

# Copy package files and install dependencies

COPY package\*.json ./
RUN npm install --only=production

# Copy application code

COPY server.js .

# Expose the port the app runs on

EXPOSE 3001

# Command to run the application

CMD [ "node", "server.js" ]