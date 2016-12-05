<?php

/*
  Copyright (c) 2016 DataStax, Inc.
  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

namespace Dse\Cluster;

/**
 */
final class DefaultBuilder extends Builder  {

    /**
     * @return mixed 
     */
    public function build() { }

    /**
     * @param mixed $consistency 
     * @return mixed 
     */
    public function withDefaultConsistency($consistency) { }

    /**
     * @param mixed $pageSize 
     * @return mixed 
     */
    public function withDefaultPageSize($pageSize) { }

    /**
     * @param mixed $timeout 
     * @return mixed 
     */
    public function withDefaultTimeout($timeout) { }

    /**
     * @param mixed $host 
     * @return mixed 
     */
    public function withContactPoints($host) { }

    /**
     * @param mixed $port 
     * @return mixed 
     */
    public function withPort($port) { }

    /**
     * @return mixed 
     */
    public function withRoundRobinLoadBalancingPolicy() { }

    /**
     * @param mixed $localDatacenter 
     * @param mixed $hostPerRemoteDatacenter 
     * @param mixed $useRemoteDatacenterForLocalConsistencies 
     * @return mixed 
     */
    public function withDatacenterAwareRoundRobinLoadBalancingPolicy($localDatacenter, $hostPerRemoteDatacenter, $useRemoteDatacenterForLocalConsistencies) { }

    /**
     * @param mixed $hosts 
     * @return mixed 
     */
    public function withBlackListHosts($hosts) { }

    /**
     * @param mixed $hosts 
     * @return mixed 
     */
    public function withWhiteListHosts($hosts) { }

    /**
     * @param mixed $dcs 
     * @return mixed 
     */
    public function withBlackListDCs($dcs) { }

    /**
     * @param mixed $dcs 
     * @return mixed 
     */
    public function withWhiteListDCs($dcs) { }

    /**
     * @param mixed $enabled 
     * @return mixed 
     */
    public function withTokenAwareRouting($enabled) { }

    /**
     * @param mixed $username 
     * @param mixed $password 
     * @return mixed 
     */
    public function withCredentials($username, $password) { }

    /**
     * @param mixed $timeout 
     * @return mixed 
     */
    public function withConnectTimeout($timeout) { }

    /**
     * @param mixed $timeout 
     * @return mixed 
     */
    public function withRequestTimeout($timeout) { }

    /**
     * @param Cassandra\SSLOptions $options 
     * @return mixed 
     */
    public function withSSL($options) { }

    /**
     * @param mixed $enabled 
     * @return mixed 
     */
    public function withPersistentSessions($enabled) { }

    /**
     * @param mixed $version 
     * @return mixed 
     */
    public function withProtocolVersion($version) { }

    /**
     * @param mixed $count 
     * @return mixed 
     */
    public function withIOThreads($count) { }

    /**
     * @param mixed $core 
     * @param mixed $max 
     * @return mixed 
     */
    public function withConnectionsPerHost($core, $max) { }

    /**
     * @param mixed $interval 
     * @return mixed 
     */
    public function withReconnectInterval($interval) { }

    /**
     * @param mixed $enabled 
     * @return mixed 
     */
    public function withLatencyAwareRouting($enabled) { }

    /**
     * @param mixed $enabled 
     * @return mixed 
     */
    public function withTCPNodelay($enabled) { }

    /**
     * @param mixed $delay 
     * @return mixed 
     */
    public function withTCPKeepalive($delay) { }

    /**
     * @param Cassandra\RetryPolicy $policy 
     * @return mixed 
     */
    public function withRetryPolicy($policy) { }

    /**
     * @param Cassandra\TimestampGenerator $generator 
     * @return mixed 
     */
    public function withTimestampGenerator($generator) { }

    /**
     * @param mixed $enabled 
     * @return mixed 
     */
    public function withSchemaMetadata($enabled) { }

    /**
     * @param mixed $username 
     * @param mixed $password 
     * @return mixed 
     */
    public function withPlaintextAuthenticator($username, $password) { }

    /**
     * @param mixed $service 
     * @param mixed $principal 
     * @return mixed 
     */
    public function withGssapiAuthenticator($service, $principal) { }

    /**
     * @param mixed $language 
     * @return mixed 
     */
    public function withGraphLanguage($language) { }

    /**
     * @param mixed $source 
     * @return mixed 
     */
    public function withGraphSource($source) { }

    /**
     * @param mixed $name 
     * @return mixed 
     */
    public function withGraphName($name) { }

    /**
     * @param mixed $consistency 
     * @return mixed 
     */
    public function withGraphReadConsistency($consistency) { }

    /**
     * @param mixed $consistency 
     * @return mixed 
     */
    public function withGraphWriteConsistency($consistency) { }

    /**
     * @param mixed $request_timeout 
     * @return mixed 
     */
    public function withGraphRequestTimeout($request_timeout) { }

}
