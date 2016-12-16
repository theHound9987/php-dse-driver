#include "php_driver.h"
#include "php_driver_types.h"

zend_class_entry *php_driver_graph_simple_statement_ce = NULL;

PHP_METHOD(GraphSimpleStatement, __construct)
{
  zval *query = NULL;
  php_driver_graph_statement *self = NULL;

  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z", &query) == FAILURE) {
    return;
  }

  if (Z_TYPE_P(query) != IS_STRING) {
    INVALID_ARGUMENT(query, "a string");
  }

  self = PHP_DRIVER_GET_GRAPH_STATEMENT(getThis());

  self->query = estrndup(Z_STRVAL_P(query), Z_STRLEN_P(query));
}

ZEND_BEGIN_ARG_INFO_EX(arginfo__construct, 0, ZEND_RETURN_VALUE, 1)
  ZEND_ARG_INFO(0, query)
ZEND_END_ARG_INFO()

static zend_function_entry php_driver_graph_simple_statement_methods[] = {
  PHP_ME(GraphSimpleStatement, __construct, arginfo__construct, ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
  PHP_FE_END
};

static zend_object_handlers php_driver_graph_simple_statement_handlers;

static HashTable *
php_driver_graph_simple_statement_properties(zval *object TSRMLS_DC)
{
  HashTable *props = zend_std_get_properties(object TSRMLS_CC);

  return props;
}

static int
php_driver_graph_simple_statement_compare(zval *obj1, zval *obj2 TSRMLS_DC)
{
  if (Z_OBJCE_P(obj1) != Z_OBJCE_P(obj2))
    return 1; /* different classes */

  return Z_OBJ_HANDLE_P(obj1) != Z_OBJ_HANDLE_P(obj1);
}

static void
php_driver_graph_simple_statement_free(php5to7_zend_object_free *object TSRMLS_DC)
{
  php_driver_graph_statement *self =
      PHP5TO7_ZEND_OBJECT_GET(graph_statement, object);

  if (self->query) {
    efree(self->query);
  }

  zend_object_std_dtor(&self->zval TSRMLS_CC);
  PHP5TO7_MAYBE_EFREE(self);
}

static php5to7_zend_object
php_driver_graph_simple_statement_new(zend_class_entry *ce TSRMLS_DC)
{
  php_driver_graph_statement *self =
      PHP5TO7_ZEND_OBJECT_ECALLOC(graph_statement, ce);

  self->query = NULL;

  PHP5TO7_ZEND_OBJECT_INIT_EX(graph_statement, graph_simple_statement, self, ce);
}

void php_driver_define_GraphSimpleStatement(TSRMLS_D)
{
  zend_class_entry ce;

  INIT_CLASS_ENTRY(ce, PHP_DRIVER_NAMESPACE "\\Graph\\SimpleStatement", php_driver_graph_simple_statement_methods);
  php_driver_graph_simple_statement_ce = zend_register_internal_class(&ce TSRMLS_CC);
  zend_class_implements(php_driver_graph_simple_statement_ce TSRMLS_CC, 1, php_driver_graph_statement_ce);
  php_driver_graph_simple_statement_ce->ce_flags     |= PHP5TO7_ZEND_ACC_FINAL;
  php_driver_graph_simple_statement_ce->create_object = php_driver_graph_simple_statement_new;

  memcpy(&php_driver_graph_simple_statement_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
  php_driver_graph_simple_statement_handlers.get_properties  = php_driver_graph_simple_statement_properties;
  php_driver_graph_simple_statement_handlers.compare_objects = php_driver_graph_simple_statement_compare;
  php_driver_graph_simple_statement_handlers.clone_obj = NULL;
}
