#include "php_dse.h"

zend_class_entry *dse_session_ce = NULL;

ZEND_BEGIN_ARG_INFO_EX(arginfo_execute, 0, ZEND_RETURN_VALUE, 1)
  ZEND_ARG_OBJ_INFO(0, statement, Cassandra\\Statement, 0)
  ZEND_ARG_OBJ_INFO(0, options, Cassandra\\ExecutionOptions, 0)
ZEND_END_ARG_INFO()

static zend_function_entry dse_session_methods[] = {
  PHP_ABSTRACT_ME(DseSession, executeGraph, arginfo_execute)
  PHP_ABSTRACT_ME(DseSession, executeGraphAsync, arginfo_execute)
  PHP_FE_END
};

void dse_define_Session(TSRMLS_D)
{
  zend_class_entry ce;

  INIT_CLASS_ENTRY(ce, "Dse\\Session", dse_session_methods);
  dse_session_ce = zend_register_internal_class(&ce TSRMLS_CC);
  zend_class_implements(dse_session_ce TSRMLS_CC, 1, cassandra_session_ce);
  dse_session_ce->ce_flags |= ZEND_ACC_INTERFACE;
}
