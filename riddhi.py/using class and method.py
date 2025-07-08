class Demo:
    def add(self,a,b):
        self.c=a+b
        print("sum",self.c)


o=Demo()
a=int(input("enter a="))
b=int(input("enter b="))
o.add(10,2)
