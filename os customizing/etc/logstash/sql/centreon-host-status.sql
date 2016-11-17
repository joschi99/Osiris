SELECT h.name, h.address, h.alias, h.display_name, h.acknowledged, h.acknowledgement_type, h.active_checks,
h.check_attempt,h.check_interval, h.check_period,h.check_type,h.checked,
h.execution_time,h.flapping,h.last_check, h.last_hard_state, h.last_hard_state_change,
h.last_notification,h.last_state_change,h.last_time_down, h.last_time_unreachable,h.last_time_up,
h.last_update,h.latency,h.max_check_attempts,h.next_check, h.next_host_notification,
h.output,h.percent_state_change,h.perfdata,h.scheduled_downtime_depth,h.state,h.state_type,h.timezone,
i.name as instance_name, i.engine as instance_engine,i.pid as instance_pid,i.start_time as instance_start_time,
i.version as instance_version,i.running as instance_running,i.last_alive as instance_last_alive,
i.last_command_check as instance_last_command_check
FROM hosts as h, instances as i
where h.enabled=1
and h.instance_id = i.instance_id
and i.deleted = 0
and h.last_check > :sql_last_value
