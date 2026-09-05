# Use official Node.js LTS lightweight image
FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy dependency files
COPY package*.json ./

# Install production dependencies
RUN npm ci --only=production

# Copy backend source code & admin dashboard
COPY . .

# Expose server port
EXPOSE 3000

# Default environment
ENV NODE_ENV=production
ENV PORT=3000

# Start server
CMD ["node", "server.js"]
