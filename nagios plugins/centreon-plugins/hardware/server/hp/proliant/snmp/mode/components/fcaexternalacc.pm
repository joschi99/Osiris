#
# Copyright 2016 Centreon (http://www.centreon.com/)
#
# Centreon is a full-fledged industry-strength solution that meets
# the needs in IT infrastructure and application monitoring for
# service performance.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

package hardware::server::hp::proliant::snmp::mode::components::fcaexternalacc;

use strict;
use warnings;

my %map_accel_status = (
    1 => 'other',
    2 => 'invalid',
    3 => 'enabled',
    4 => 'tmpDisabled',
    5 => 'permDisabled',
);
my %map_accel_condition = (
    1 => 'other', 
    2 => 'ok', 
    3 => 'degraded', 
    4 => 'failed',
);
my %map_accelbattery_condition = (
    1 => 'other', 
    2 => 'ok', 
    3 => 'recharging', 
    4 => 'failed',
    5 => 'degraded',
    6 => 'not present',
);

# In 'CPQFCA-MIB.mib'    
my $mapping = {
    cpqFcaAccelStatus => { oid => '.1.3.6.1.4.1.232.16.2.2.2.1.3', map => \%map_accel_status },
};
my $mapping2 = {
    cpqFcaAccelCondition => { oid => '.1.3.6.1.4.1.232.16.2.2.2.1.9', map => \%map_accel_condition },
};
my $mapping3 = {
    cpqFcaAccelBatteryStatus => { oid => '.1.3.6.1.4.1.232.16.2.2.2.1.6', map => \%map_accelbattery_condition },
};
my $oid_cpqFcaAccelStatus = '.1.3.6.1.4.1.232.16.2.2.2.1.3';
my $oid_cpqFcaAccelCondition = '.1.3.6.1.4.1.232.16.2.2.2.1.9';
my $oid_cpqFcaAccelBatteryStatus = '.1.3.6.1.4.1.232.16.2.2.2.1.6';

sub load {
    my (%options) = @_;
    
    push @{$options{request}}, { oid => $oid_cpqFcaAccelStatus };
    push @{$options{request}}, { oid => $oid_cpqFcaAccelCondition };
    push @{$options{request}}, { oid => $oid_cpqFcaAccelBatteryStatus };
}

sub check {
    my ($self) = @_;
    
    $self->{output}->output_add(long_msg => "Checking fca external accelerator boards");
    $self->{components}->{fcaexternalacc} = {name => 'fca external accelerator boards', total => 0, skip => 0};
    return if ($self->check_exclude(section => 'fcaexternalacc'));
    
    foreach my $oid ($self->{snmp}->oid_lex_sort(keys %{$self->{results}->{$oid_cpqFcaAccelCondition}})) {
        next if ($oid !~ /^$mapping->{cpqFcaAccelCondition}->{oid}\.(.*)$/);
        my $instance = $1;
        my $result = $self->{snmp}->map_instance(mapping => $mapping, results => $self->{results}->{$oid_cpqFcaAccelStatus}, instance => $instance);
        my $result2 = $self->{snmp}->map_instance(mapping => $mapping2, results => $self->{results}->{$oid_cpqFcaAccelCondition}, instance => $instance);
        my $result3 = $self->{snmp}->map_instance(mapping => $mapping3, results => $self->{results}->{$oid_cpqFcaAccelBatteryStatus}, instance => $instance);

        next if ($self->check_exclude(section => 'fcaexternalacc', instance => $instance));
        $self->{components}->{fcaexternalacc}->{total}++;

        $self->{output}->output_add(long_msg => sprintf("fca external accelerator boards '%s' [status: %s, battery status: %s] condition is %s.", 
                                    $instance, 
                                    $result->{cpqFcaAccelStatus}, $result3->{cpqFcaAccelBatteryStatus},
                                    $result2->{cpqFcaAccelCondition}));
        my $exit = $self->get_severity(section => 'fcaexternalacc', value => $result2->{cpqFcaAccelCondition});
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1)) {
            $self->{output}->output_add(severity => $exit,
                                        short_msg => sprintf("fca external accelerator boards '%s' is %s", 
                                            $instance, $result2->{cpqFcaAccelCondition}));
        }
        $exit = $self->get_severity(section => 'fcaexternalaccbattery', value => $result3->{cpqFcaAccelBatteryStatus});
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1)) {
            $self->{output}->output_add(severity => $exit,
                                        short_msg => sprintf("fca external accelerator boards '%s' battery is %s", 
                                            $instance, $result3->{cpqFcaAccelBatteryStatus}));
        }
    }
}

1;