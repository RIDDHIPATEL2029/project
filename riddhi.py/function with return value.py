def first_add(x):
    def second_add(y):
        return x+y
    return second_add
i=first_add(20)
print("the value of x+y is",i(10))

def outer_func(x):
    return x*5
def func():
    return outer_func
z=func()
print("\nthe value of x*y is",z(10))
