NeDi - 1.5.225
==============

Introduction
------------
NeDi discovers, maps and inventories your network devices and tracks connected end-nodes.
It contains a lot of features in a user-friendly GUI for managing enterprise networks.
For example: MAC address mapping/tracking, traffic & error graphing, uptime monitoring,
correlate collected syslog & trap messages with customizable notification, drawing
network maps, extensive reporting features such as device software, PoE usage, disabled
interfaces, link errors, switch usage and many more. It's modular architecture allows for
simple integration with other tools. For example Cacti graphs can be created purely based
on discovered information. Due to NeDi's versatility things like printer resources can be
monitored as well...

Changes from 1.4.300
--------------------
"under-the-hood" Enhancements:
- Added -O to select nodes for discovery (like -A)
- Added skip option -SX to skip existing devs (ignored for -A)
- Added test option -ts to exit after generating seeds
- Added a device install option -T to allow provisioning (only tested with HP ProCurve at this stage)
- Added retrieving ARP and config backup via ssh for Sonicwall devices
- Added UCS support for config backup and nodes via CLI
- Added Ruckus Zone Director support
- Added a flexible policy engine (alert on configuration violation or nodes/neighbors for example)
- Added device vendors for easier access and filtering
- Added LAG-MIB support to associate physical interfaces with port-channels/trunks
- Added  support for Unify VoIP phones
- Added user password security (with passphrase) in nedi.conf
- Added -x (callout) support for nodes
- Added power status and location to modules (goal is to show distributed stacks and FEXes in racks)
- Improved UPS support
- Imporoved ARP/ND tracking and DNS update logic to minimize duplicates
- Improved usepoe to be configurable per device type
- Removed error warning (>1/min but <1/sec). Those interfaces can still appear in the "Top Error Ports" report
- Replaced "scan" option using nmap with internal host identification method
- Interface (and VRF/VPN) status is reflected in the networks table now
- Changed start of IOS configs to "version" for PXE boot compatibility
- Started adding LLDP for supported Cisco devices (ongoing process)
- Setting MACflood threshold in Devices-Interfaces allows for multiple neighbors on an interface
- Moved nosnmp regexp for supported APs and phones to libsnmp.pm, making it easier configure unsupported devices

GUI Enhancements:
- Added Reports-Custom providing flexible reporting acrosse the whole database
- Added System-Install for managing provisioning slots
- Added System-Policies which replaces Nodes-Track and Nodes-Stolen
- Added event acknowledgemnt for managers
- Added simplified reports as default for some GUI-modules (if List Optimize is set in User-Profile)
- Added DB comparison to Other-Calculator to identify available subnets
- Added Reports-Networks and moved related reports from Reports-Interfaces
- Enhanced System-Files for managing CLI-command files and install templates
- Enhanced Topology-Table (e.g. showing device count and nicer printout)
- Enhanced vendor features and added more vendor icons
- Enhanced Asset management (added CSV import and batch editing for example)
- Enhanced some GUI-modules to hide the input form by clicking on the header
- Refactored Monitoring-Health to work with thousands of targets (disabled IF had to go)
- Refactored index.php for a cleaner authentication process
- Execution of CLI-command files from Devices-Status
- Giving more rights to Manager (mgr) role (e.g. discover single device)
- Moved default output log in System-Files from /tmp to /var/log/nedi
- Rearranged GUI-module names (e.g. Asset-Management) to align with new ones
- Finished html5 compliance (pls report quirks I've missed)
- New default theme and removal of some old ones
- Completed English help system rewrite
- Many smaller bug fixes and optimizations

DB Changes
----------
Updating the DB is very easy with mysql:
- "nedi.pl -i updatedb" updates the DB from 1.4.300

Due to restrictions entries in the monitoring table is not preserved with postgres backend. Create a backup prior updating!
