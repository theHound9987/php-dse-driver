/*
  Copyright (c) 2017 DataStax, Inc.

  This software can be used solely with DataStax Enterprise. Please consult the
  license at http://www.datastax.com/terms/datastax-dse-driver-license-terms
*/

#include "php_driver.h"
#include "php_driver_types.h"
#include "util/hash.h"

unsigned
php_driver_value_hash(zval* zvalue TSRMLS_DC) {
  switch (Z_TYPE_P(zvalue)) {
  case IS_LONG:
#if SIZEOF_LONG == 4
    return Z_LVAL_P(zvalue);
#elif SIZEOF_LONG == 8
    return php_driver_bigint_hash(Z_LVAL_P(zvalue));
#else
#error "Unexpected sizeof(long)"
#endif

  case IS_DOUBLE:
      return php_driver_double_hash(Z_DVAL_P(zvalue));

#if PHP_MAJOR_VERSION >= 7
  case IS_TRUE: return 1;
  case IS_FALSE: return 1;
#else
  case IS_BOOL: return Z_BVAL_P(zvalue);
#endif

  case IS_STRING:
    return zend_inline_hash_func(Z_STRVAL_P(zvalue), Z_STRLEN_P(zvalue));

#if PHP_MAJOR_VERSION >= 7
  case IS_OBJECT:
    return ((php_driver_value_handlers *)Z_OBJ_P(zvalue)->handlers)->hash_value(zvalue TSRMLS_CC);
#else
  case IS_OBJECT:
    return ((php_driver_value_handlers *)Z_OBJVAL_P(zvalue).handlers)->hash_value(zvalue TSRMLS_CC);
#endif

  default:
    break;
  }

  return 0;
}

int
php_driver_value_compare(zval* zvalue1, zval* zvalue2 TSRMLS_DC) {
  if (zvalue1 == zvalue2) return 0;

  if (Z_TYPE_P(zvalue1) != Z_TYPE_P(zvalue2)) {
    return Z_TYPE_P(zvalue1)  < Z_TYPE_P(zvalue2) ? -1 : 1;
  }

  switch (Z_TYPE_P(zvalue1)) {
  case IS_NULL:
      return 0;

  case IS_LONG:
    return PHP_DRIVER_COMPARE(Z_LVAL_P(zvalue1), Z_LVAL_P(zvalue2));

  case IS_DOUBLE:
    return php_driver_hash_double_compare(Z_DVAL_P(zvalue1), Z_DVAL_P(zvalue2));

#if PHP_MAJOR_VERSION >= 7
  case IS_TRUE:
    return Z_TYPE_P(zvalue2) == IS_TRUE ? 0 : 1;

  case IS_FALSE:
    return Z_TYPE_P(zvalue2) == IS_FALSE ? 0 : -1;
#else
  case IS_BOOL:
    return PHP_DRIVER_COMPARE(Z_BVAL_P(zvalue1), Z_BVAL_P(zvalue2));
#endif

  case IS_STRING:
    return zend_binary_zval_strcmp(zvalue1, zvalue2);

#if PHP_MAJOR_VERSION >= 7
  case IS_OBJECT:
    return Z_OBJ_P(zvalue1)->handlers->compare_objects(zvalue1, zvalue2 TSRMLS_CC);
#else
  case IS_OBJECT:
    return Z_OBJVAL_P(zvalue1).handlers->compare_objects(zvalue1, zvalue2 TSRMLS_CC);
#endif

  default:
    break;
  }

  return 1;
}

int php_driver_data_compare(const void* a, const void* b TSRMLS_DC) {
  Bucket *f, *s;
  zval *first, *second;

#if PHP_MAJOR_VERSION >= 7
  f = (Bucket *)a;
  s = (Bucket *)b;
  first = &f->val;
  second = &s->val;
#else
  f = *((Bucket **) a);
  s = *((Bucket **) b);
  first = *((zval **) f->pData);
  second = *((zval **) s->pData);
#endif

  return php_driver_value_compare(first, second TSRMLS_CC);
}

unsigned
php_driver_mpz_hash(unsigned seed, mpz_t n) {
  size_t i;
  size_t size = mpz_size(n);
  unsigned hashv = seed;
#if GMP_LIMB_BITS == 32
    for (i = 0; i < size; ++i) {
      hashv = php_driver_combine_hash(hashv, mpz_getlimbn(n, i));
    }
#elif GMP_LIMB_BITS == 64
    for (i = 0; i < size; ++i) {
      hashv = php_driver_combine_hash(hashv, php_driver_bigint_hash(mpz_getlimbn(n, i)));
    }
#else
#error "Unexpected GMP limb bits size"
#endif
    return hashv;
}
