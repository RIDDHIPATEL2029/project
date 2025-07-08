class parent:
    def func1(self):
        print("this is parent class function")
class child(parent):
    def func2(self):
        print("this is child class function")

o=child()
o.func1()
o.func2()
