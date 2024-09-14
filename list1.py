#list1=[1,2,3,4,5,6]
#for i in list1:
  #  print(i)
'''
def find_sum(*numbers):
    result=0
    for num in numbers:
        result=result+num
    print("sum=",result)
find_sum(1,2,3,4,5,6,7,8,9)
find_sum(4,9)
'''
def add(a=10,b=9):
    sum=a+b
    print(sum)
add(2,3)
add(10)
add()