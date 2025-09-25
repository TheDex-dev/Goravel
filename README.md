# Server Management Scripts

This directory contains optimized scripts for managing both Laravel and Go servers in the Pendataan IGD migration project.

## 🚀 Quick Start

```bash
# Start both servers
./start_servers.sh

# Check server health
./health_check.sh

# Stop both servers
./stop_servers.sh
```

## 📋 Available Scripts

### 🟢 start_servers.sh
**Optimized server startup script**

Features:
- ✅ Dependency checking (Go, PHP, binaries)
- ✅ Port conflict resolution
- ✅ Automatic Go server building if needed
- ✅ Health checks and readiness waiting
- ✅ PID tracking for proper management
- ✅ Colored output and progress indicators
- ✅ Graceful shutdown on Ctrl+C
- ✅ Detailed status information

```bash
./start_servers.sh          # Start both servers
./start_servers.sh status    # Show server status
./start_servers.sh help      # Show help
```

### 🔴 stop_servers.sh
**Graceful server shutdown script**

Features:
- ✅ PID-based graceful shutdown
- ✅ Port-based fallback killing
- ✅ Force stop capability
- ✅ Status verification after shutdown
- ✅ Cleanup of PID files and logs

```bash
./stop_servers.sh           # Stop servers gracefully
./stop_servers.sh force     # Force stop all servers
./stop_servers.sh status    # Check if servers are running
```

### 🏥 health_check.sh
**Server health monitoring script**

Features:
- ✅ Health endpoint checking
- ✅ Response time measurement
- ✅ Detailed health information
- ✅ Continuous monitoring mode
- ✅ Quick status overview

```bash
./health_check.sh           # Detailed health check
./health_check.sh quick     # Quick status
./health_check.sh monitor   # Continuous monitoring
```

### 🐹 serve_go.sh
**Individual Go server script** (Enhanced)

Features:
- ✅ Automatic binary building
- ✅ Error handling and validation
- ✅ Proper logging to file
- ✅ Colored output

### 🐘 serve_laravel.sh
**Individual Laravel server script** (Enhanced)

Features:
- ✅ Dependency checking
- ✅ Environment file validation
- ✅ Error handling
- ✅ Proper logging to file

## 🔧 Configuration

### Default Ports
- **Go API Server**: `8080`
- **Laravel Server**: `8000`

### File Locations
- Go binary: `goserver/goserver`
- Go logs: `goserver/log.txt`
- Laravel logs: `laravel/storage/logs/serve.log`
- Server PIDs: `server_pids.txt`

## 🌟 Key Improvements

### 1. **Reliability**
- Port conflict detection and resolution
- Dependency validation before startup
- Health checks to ensure servers are ready
- Proper error handling and recovery

### 2. **User Experience**
- Colored output for better readability
- Progress indicators during startup
- Detailed status information
- Helpful error messages and suggestions

### 3. **Management**
- PID tracking for proper process management
- Graceful shutdown with fallback options
- Log file organization
- Status monitoring capabilities

### 4. **Development Workflow**
- Automatic building of Go server when needed
- Environment validation for Laravel
- Quick health checks for debugging
- Integration with existing migration tests

## 🚨 Troubleshooting

### Common Issues

**Port already in use:**
```bash
./stop_servers.sh force    # Force kill processes on ports
./start_servers.sh         # Try starting again
```

**Go server won't build:**
```bash
cd goserver
make clean
make build
```

**Laravel dependencies missing:**
```bash
cd laravel
composer install
```

**Permission denied:**
```bash
chmod +x *.sh              # Make scripts executable
```

### Monitoring

**Check server logs:**
```bash
# Go server logs
tail -f goserver/log.txt

# Laravel server logs  
tail -f laravel/storage/logs/serve.log

# Laravel application logs
tail -f laravel/storage/logs/laravel.log
```

**Monitor servers continuously:**
```bash
./health_check.sh monitor
```

## 🔗 Integration

These scripts work seamlessly with:
- `./test_migration.sh` - Migration testing
- `goserver/Makefile` - Go development commands
- Laravel Artisan commands
- Docker Compose setup (when needed)

## 📊 Server Information

When servers are running, you'll have access to:

### Go API Server (Port 8080)
- Health endpoint: `http://localhost:8080/api/health`
- API documentation: Available through Laravel proxy
- Direct API access for testing

### Laravel Server (Port 8000)  
- Web interface: `http://localhost:8000`
- API proxy: Routes to Go server when configured
- Legacy endpoints: Maintained for backward compatibility

## 🎯 Usage Patterns

### Development Workflow
```bash
# Start development
./start_servers.sh

# Monitor health (in another terminal)
./health_check.sh monitor

# Run tests
./test_migration.sh

# Stop when done
./stop_servers.sh
```

### Debugging Issues
```bash
# Check what's running
./health_check.sh

# Stop everything
./stop_servers.sh force

# Check logs
tail -f goserver/log.txt
tail -f laravel/storage/logs/serve.log

# Restart
./start_servers.sh
```

### Production-like Testing
```bash
# Clean start
./stop_servers.sh force
./start_servers.sh

# Verify everything works
./health_check.sh
./test_migration.sh

# Monitor performance
./health_check.sh monitor
```

---

*These optimized scripts provide a robust foundation for managing your Laravel to Go migration project with improved reliability, better user experience, and comprehensive monitoring capabilities.*