FROM node:23-alpine

WORKDIR /app

# Copy dependencies files first
COPY package.json package-lock.json ./

# Install dependencies
RUN npm install --only=production

# Copy application code
COPY server.js .

# Expose the backend service port
EXPOSE 3001

# Set environment variable for API host
ENV API_HOST=0.0.0.0

# Command to run the Node.js app
CMD [ "node", "server.js" ]
